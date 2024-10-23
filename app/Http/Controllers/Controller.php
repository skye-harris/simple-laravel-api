<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorisedException;
use App\Helpers\Strings;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class Controller
{
    /**
     * Default throwable handler for common-use cases
     * @param Throwable $throwable
     * @return Response
     */
    public function handleThrowableResponse(Throwable $throwable): Response
    {
        $response = new Response();

        switch (get_class($throwable)) {
            case UnauthorisedException::class:
                // Handle for update/delete requests being made against resources that the user does not own
                $response->setStatusCode(401)
                         ->setContent(Strings::UNAUTHORISED);
                break;

            case NotFoundException::class:
                // Handle for requests for non-existent resources
                $response->setStatusCode(404)
                         ->setContent(Strings::RESOURCE_NOT_FOUND);
                break;

            case ValidationException::class:
                // Send back validation errors as a regular string
                $validator = $throwable->validator;
                $errors = array_reduce(
                    $validator->errors()->toArray(),
                    static function ($accumulator, $data) {
                        $accumulator[] = $data[0];

                        return $accumulator;
                    },
                    []
                );

                $response->setStatusCode(400)
                         ->setContent(
                             implode(' ', $errors)
                         );
                break;

            default:
                // A default generic error to return, where we have nothing better to send
                $response->setStatusCode(500)
                         ->setContent(Strings::DEFAULT_ERROR_MESSAGE);
                break;
        }

        return $response;
    }
}
