# Laravel API Sample Project

This sample project implements a simple blog platform with following API endpoints:

| Method | Endpoint                      | Authorised | Description                                                                                                                                                          |
|--------|-------------------------------|------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| GET    | /api/posts                    | Yes        | Retrieve all posts, paginated in pages of 10. Pass the 'page' Query Parameter to request a specific page. If not provided, will default to page 1.                   |
| GET    | /api/posts/{id}               | Yes        | Retrieve a specific post by ID.                                                                                                                                      |
| POST   | /api/posts                    | Yes        | Create a new post. Title and Content fields must be provided.                                                                                                        |
| PATCH  | /api/posts/{id}               | Yes        | Updated an existing post. Title and Content fields must be provided. Must be logged-in as the owner of the post.                                                     |
| DELETE | /api/posts/{id}               | Yes        | Delete an existing post. Must be logged-in as the owner of the post.                                                                                                 |
| GET    | /api/posts/{id}/comments      | Yes        | Retrieve all comments for a post, paginated in pages of 10. Pass the 'page' Query Parameter to request a specific page. If not provided, will default to page 1.     |
| POST   | /api/posts/{id}/comments      | Yes        | Create a new comment on a post. Content field must be provided.                                                                                                      |
| PATCH  | /api/posts/{id}/comments/{id} | Yes        | Update an existing comment on a post. Content field must be provided. ust be logged-in as the owner of the comment.                                                  |
| DELETE | /api/posts/{id}/comments/{id} | Yes        | Delete an existing comment. Must be logged-in as the owner of the comment or the post that it was created on.                                                        |
| GET    | /api/users/{id}               | Yes        | Retrieve public data for a User.                                                                                                                                     |
| POST   | /api/users/register           | No         | Register a new User account. Name, Email, and Password fields must be provided. A Verification Email will be Queued to send to this User when the Queue is next run. |
| POST   | /api/users/login              | No         | Login as an existing User. Email and Password fields must be provided. Provides a Bearer Token upon success, to be used for all Authorised requests.                 |
| POST   | /api/users/logout             | Yes        | Logout the current User session.                                                                                                                                     |

Additionally, a single Web endpoint exists on which to validate user registrations:
* `/users/activate?t={activation token}` Accepts a token to unique identify and validate a user registration.

## Environment Setup

This project utilises Docker & DDEV for development services.

### Pre-requisites

* Docker
* DDEV https://ddev.com/

### Setup Steps

* `make ddev-init` to initialise the DDEV environment, install Composer dependencies, and build the database. The DDEV
  environment will be assigned the hostname `laravel-api-demo.ddev.site`
* `make db-seed` if you wish to seed some test data into the database. This is optional.

At this point, the project should be ready and accessible.

### Make Commands

A number of additional Make commands have been created to assist with controlling and running tasks within the DDEV
container

* `make ddev-start` Start the DDEV environment, if not already running
* `make ddev-stop` Stop the DDEV environment, if running
* `make ddev-destroy` Destroy the DDEV environment and remove the local .ddev/ and vendor/ directories
* `make ddev-artisan ARG="artisan:command"` Run the specified Artisan command, eg "artisan:command", within the DDEV
  environment
* `make run-queue` Execute pending Queue jobs
* `make run-tests` Execute PHPUnit Tests
* `make welcome-email-test {id}` Send a Welcome Email test to the user specified by `{id}`. This will then run the Queue
  jobs to send off any pending emails. If using the `log` Mailer, the User Activation link can be retrieved from `storage/logs/laravel.log` 
