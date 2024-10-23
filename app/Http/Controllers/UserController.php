<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\RegistrationException;
use App\Helpers\Strings;
use App\Jobs\UserVerificationEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Throwable;

class UserController extends Controller
{
    public function login(Request $request): Response
    {
        $response = new Response();

        try {
            // Ensure that our parameters have been provided
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $emailAddress = $request->get('email');
            $password = $request->get('password');

            // Before we authenticate, lets ensure this account exists
            /** @var User $user */
            $user = User::where('email', $emailAddress)->first();
            if (!$user) {
                throw new AuthenticationException();
            }

            // And has been verified
            if (!$user->hasVerifiedEmail()) {
                throw new RegistrationException();
            }

            // And their password checks out
            if (!Hash::check($password, $user->password)) {
                throw new AuthenticationException();
            }

            // Create a Sanctum auth token for this User
            $token = $user->createToken('access_token');

            $response->setContent([
                'token' => $token->plainTextToken
            ]);

            return $response;
        } catch (AuthenticationException $exception) {
            // Handle our Password Validation failure

            $response->setStatusCode(401)
                ->setContent(Strings::LOGIN_FAILURE);

            return $response;
        } catch (RegistrationException $exception) {
            // Handle for Account not having been verified

            $response->setStatusCode(403)
                ->setContent(Strings::REGISTER_VALIDATE);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function logout(Request $request): Response
    {
        $response = new Response();

        try {
            // Revoke our access token
            $request->user()->currentAccessToken()->delete();

            // Fill in our template message with the users email
            $response->setContent(Strings::LOGOUT_SUCCESS);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function register(Request $request): Response
    {
        $response = new Response();

        try {
            // Ensure that our parameters have been provided
            $request->validate([
                'name' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $password = $request->get('password');
            $emailAddress = $request->get('email');
            $name = $request->get('name');

            // Password complexity compliance - at least 8 characters in length, 1 digit, 1 uppercase, 1 lowercase
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
                throw new RegistrationException(Strings::REGISTER_PASSWORD_COMPLEXITY);
            }

            // Check for existing user
            /** @var User $existingUser */
            $existingUser = User::where('email', $emailAddress)->first();

            if ($existingUser) {
                throw new RegistrationException(Strings::REGISTER_EXISTING_USER);
            }

            // Create a v4 UUID for our validation token, we'll be storing this as a binary(16) blob for efficiency
            $uuid = Uuid::uuid4();
            $uuidBytes = $uuid->getBytes();

            // Create user
            /** @var User $user */
            $user = User::create([
                'name' => $name,
                'email' => $emailAddress,
                'password' => Hash::make($password),
                'email_verification_token' => $uuidBytes,
            ]);

            // Send validation email
            UserVerificationEmail::dispatch($user);

            // Fill in our template message with the users email
            $responseMessage = sprintf(Strings::REGISTER_SUCCESS_TEMPLATE, $emailAddress);
            $response->setContent($responseMessage);

            return $response;
        } catch (RegistrationException $exception) {
            // Handle for our misc Registration failures

            $response->setStatusCode(400)
                     ->setContent($exception->getMessage());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function activate(Request $request): Response {
        $response = new Response();

        try {
            // Ensure that our parameters have been provided
            $request->validate([
                't' => ['required', 'string'],
            ]);

            $token = $request->get('t');
            $token = base64_decode($token);

            [$emailValidationToken, $emailAddress] = explode(':',$token);
            $tokenUuid = Uuid::fromString($emailValidationToken);
            $uuidBytes = $tokenUuid->getBytes();

            /** @var User $user */
            $user = User::where([
                'email' => $emailAddress,
            ])->first();

            if (!$user || $user->email_verification_token !== $uuidBytes) {
                throw new RegistrationException();
            }

            $user->update([
                'email_verified_at' => Carbon::now(),
                'email_verification_token' => null,
            ]);

            $contentView = view('user.activation_success');
            $response->setContent($contentView);

            return $response;
        } catch (RegistrationException $exception) {
            // We were unable to match an account successfully

            $contentView = view('user.activation_failure');
            $response->setContent($contentView);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function getUser(Request $request, int $id): Response
    {
        $response = new Response();

        try {
            /** @var User $user */
            $user = User::find($id);

            if (!$user) {
                throw new NotFoundException();
            }

            $response->setContent($user->toJson());

            return $response;
        } catch (NotFoundException $exception) {
            // Handle for the requested user not existing

            $response->setStatusCode(404)
                ->setContent(Strings::RESOURCE_NOT_FOUND);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }
}
