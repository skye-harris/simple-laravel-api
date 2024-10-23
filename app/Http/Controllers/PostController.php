<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorisedException;
use App\Helpers\Strings;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PostController extends Controller
{
    private const PAGE_SIZE = 10;

    public function getSingular(Request $request, int $id): Response
    {
        $response = new Response();

        try {
            // Retrieve and return a singular post

            /** @var Post $post */
            $post = Post::find($id);
            if (!$post) {
                throw new NotFoundException();
            }

            $response->setContent($post->toJson());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function getPaginated(Request $request): Response
    {
        $response = new Response();

        try {
            $request->validate([
                'page' => ['integer', 'min:1'],
            ]);

            // Retrieve our page variable and query our data
            $page = $request->get('page', 1);
            $pagePosts = Post::query()
                             ->orderBy('id', 'ASC')
                             ->offset(($page - 1) * static::PAGE_SIZE)
                             ->limit(static::PAGE_SIZE)
                             ->get();

            $responseContent = [
                'posts' => $pagePosts->toArray(),
                'current_page' => $page,
                'total_pages' => ceil(Post::count() / static::PAGE_SIZE),
            ];

            $response->setContent(json_encode($responseContent));

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function create(Request $request): Response
    {
        $response = new Response();

        try {
            // Validate our data
            $request->validate([
                'title' => ['required', 'string', 'max:255', 'min:1'],
                'content' => ['required', 'string', 'max:65535', 'min:1'],
            ]);

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // Create our Post
            /** @var Post $post */
            $post = Post::create([
                'title' => $request->get('title'),
                'content' => $request->get('content'),
                'user_id' => $user->id,
            ]);

            $response->setContent($post->toJson());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function update(Request $request, int $id): Response
    {
        $response = new Response();

        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255', 'min:1'],
                'content' => ['required', 'string', 'max:65535', 'min:1'],
            ]);

            // First lets locate the post to update
            /** @var Post $post */
            $post = Post::find($id);
            if (!$post) {
                throw new NotFoundException();
            }

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // And check if our user is the post owner
            if ($post->user_id !== $user->id) {
                throw new UnauthorisedException();
            }

            // Update our post
            $post->update([
                'title' => $request->get('title'),
                'content' => $request->get('content')
            ]);

            $response->setContent($post->toJson());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function delete(Request $request, int $id): Response
    {
        $response = new Response();

        try {
            // First lets locate the post to delete
            /** @var Post $post */
            $post = Post::find($id);
            if (!$post) {
                throw new NotFoundException();
            }

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // And check if our user is the post owner
            if ($post->user_id !== $user->id) {
                throw new UnauthorisedException();
            }

            // Delete our post
            $post->delete();

            $response->setContent(Strings::POST_DELETED);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }
}
