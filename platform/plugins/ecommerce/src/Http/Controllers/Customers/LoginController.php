<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Botble\ACL\Traits\AuthenticatesUsers;
use Botble\ACL\Traits\LogoutGuardTrait;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\JsValidation\Facades\JsValidator;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use GuzzleHttp\Client;
use http\Client\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers, LogoutGuardTrait {
        AuthenticatesUsers::attemptLogin as baseAttemptLogin;
    }

    public string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('customer.guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
        SeoHelper::setTitle(__('Login'));

        Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(__('Login'), route('customer.login'));

        if (! session()->has('url.intended') &&
            ! in_array(url()->previous(), [route('customer.login'), route('customer.register')])
        ) {
            session(['url.intended' => url()->previous()]);
        }

       /* Theme::asset()
            ->container('footer')
            ->usePath(false)
            ->add('js-validation', 'vendor/core/core/js-validation/js/js-validation.js', ['jquery']);*/

        add_filter(THEME_FRONT_FOOTER, function ($html) {
            return $html . JsValidator::formRequest(LoginRequest::class)->render();
        });

        return Theme::scope('ecommerce.customers.login', [], 'plugins/ecommerce::themes.customers.login')->render();
    }

    public function memberLoginValidation($memberId, $sid, $password){
        try{
            $url = "https://www.coopeastngr.com/api/memval.asp?uid={$memberId}&sid={$sid}&pw={$password}";
            $client = new Client();
            return $client->get($url);
        }catch (\Exception $exception){
            return 'exception';
        }
    }

    protected function guard()
    {
        return auth('customer');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, (new LoginRequest())->rules());
    }

    public function login(Request $request)
    {
     /*   $this->validate($request,[
            'email'=>'required',
            'password'=>'required'
        ]);
        return dd('here');*/

        $this->validateLogin($request);


        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }


        $sid = '999999';
        $response = $this->memberLoginValidation($request->email, $sid, $request->password);
        $response_data =  json_decode((string) $response->getBody(), true);
        $collection = collect($response_data);

        if($collection['response'] == '200: OK') {
            $response_data = json_decode((string)$response->getBody(), true);
            $collection = collect($response_data);
            $email = $collection['email'];
            $name = $collection['name'];
            $checkCustomer = Customer::getUserByEmail($email);
            if(!empty($checkCustomer)) {
                //Update login credentials should in case there's any change
                $checkCustomer->email = $email;
                $checkCustomer->password = bcrypt($request->password);
                $checkCustomer->save();

                $request['email'] = $email;
                $request['password'] = $request->password;

            }else{
                //Create user account
                $request['name'] = $name;
                $request['member_id'] = $request->email; //the input name is actually email. Though it's for member ID
                $request['email'] = $email;
                $request['password'] = $request->password;
                $this->register($request);
            }
        }


        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to log in and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $this->sendFailedLoginResponse();
    }

    public function register(Request $request/*, BaseHttpResponse $response*/)
    {
        $this->validator($request->input())->validate();

        do_action('customer_register_validation', $request);

        $customer = $this->create($request->input());

        event(new Registered($customer));
        session()->flash("success", "New account registered! Proceed to login.");
        return redirect()->route('customer.login');

       /* if (EcommerceHelper::isEnableEmailVerification()) {
            $this->registered($request, $customer);

            return $response
                ->setNextUrl(route('customer.login'))
                ->setMessage(__('We have sent you an email. Please verify your email. Please check and confirm your email address!'));
        }*/

        $customer->confirmed_at = Carbon::now();
        $customer->save();

        $this->guard()->login($customer);

        return $response->setNextUrl($this->redirectPath())->setMessage(__('Registered successfully!'));
    }

    protected function create(array $data)
    {
        return Customer::query()->create([
            'name' => BaseHelper::clean($data['name']),
            'email' => BaseHelper::clean($data['email']),
            'member_id' => BaseHelper::clean($data['member_id']),
            'password' => Hash::make($data['password']),
        ]);
    }


    public function logout(Request $request)
    {
        $this->guard()->logout();

        $this->loggedOut($request);

        return redirect()->to(route('public.index'));
    }

    protected function attemptLogin(Request $request)
    {
        if ($this->guard()->validate($this->credentials($request))) {
            $customer = $this->guard()->getLastAttempted();

            if (EcommerceHelper::isEnableEmailVerification() && empty($customer->confirmed_at)) {
                throw ValidationException::withMessages([
                    'confirmation' => [
                        __(
                            'The given email address has not been confirmed. <a href=":resend_link">Resend confirmation link.</a>',
                            [
                                'resend_link' => route('customer.resend_confirmation', ['email' => $customer->email]),
                            ]
                        ),
                    ],
                ]);
            }

            if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
                throw ValidationException::withMessages([
                    'email' => [
                        __('Your account has been locked, please contact the administrator.'),
                    ],
                ]);
            }

            return $this->baseAttemptLogin($request);
        }

        return false;
    }
}
