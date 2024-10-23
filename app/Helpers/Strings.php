<?php

namespace App\Helpers;

class Strings
{
    // Default message if we have nothing better to provide
    public const DEFAULT_ERROR_MESSAGE = 'An unexpected error has occurred';

    public const LOGIN_FAILURE = 'Login failed. No accounts found that match the provided credentials';
    public const LOGOUT_SUCCESS = 'Successfully logged-out';

    public const REGISTER_EXISTING_USER = 'An existing user exists with this email address';
    public const REGISTER_PASSWORD_COMPLEXITY = 'Account passwords must be at least 8 characters in length, and contain at least: 1 lowercase letter, 1 uppercase letter, and 1 number';
    public const REGISTER_SUCCESS_TEMPLATE = 'Successfully registered account "%s". Please check your email for an Account Validation email in order to verify your account';
    public const REGISTER_VALIDATE = 'This account has not been verified. Please check your email for an Account Verification email in order to verify your account';

    public const UNAUTHORISED = 'You are not authorised to make this request. Resources can only be updated by their owner';
    public const RESOURCE_NOT_FOUND = 'The specified resource could not be found';

    public const POST_DELETED = 'Post has been deleted successfully';
    public const COMMENT_DELETED = 'Comment has been deleted successfully';
}
