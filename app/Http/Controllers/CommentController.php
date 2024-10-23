<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorisedException;
use App\Helpers\Strings;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class CommentController extends Controller
{
    private const PAGE_SIZE = 10;

    /**
     * @throws NotFoundException
     */
    private function getPost(int $postId): Post {
        /** @var Post $post */
        $post = Post::find($postId);

        if (!$post) {
            throw new NotFoundException();
        }

        return $post;
    }

    /**
     * @throws NotFoundException
     */
    private function getPostComment(Post $post, int $commentId): Comment {
        /** @var Comment $comment */
        $comment = $post->comments()->where('id', $commentId)->first();

        if (!$comment) {
            throw new NotFoundException();
        }

        return $comment;
    }

    public function getPaginated(Request $request, int $postId): Response
    {
        $response = new Response();

        try {
            $request->validate([
                'page' => ['integer', 'min:1'],
            ]);

            // Retrieve our page variable and query our data
            $page = $request->get('page', 1);

            // Retrieve our post from the URL variable, will throw upon failure
            $post = $this->getPost($postId);

            $pageComments = $post->comments()
                                 ->orderBy('id', 'ASC')
                                 ->offset(($page - 1) * static::PAGE_SIZE)
                                 ->limit(static::PAGE_SIZE)
                                 ->get();

            $responseContent = [
                'comments' => $pageComments->toArray(),
                'current_page' => $page,
                'total_pages' => ceil($post->comments()->count() / static::PAGE_SIZE),
            ];

            $response->setContent(json_encode($responseContent));

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function create(Request $request, int $postId): Response
    {
        $response = new Response();

        try {
            // Validate our data
            $request->validate([
                'content' => ['required', 'string', 'max:65535', 'min:1'],
            ]);

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // Retrieve our post from the URL variable, will throw upon failure
            $post = $this->getPost($postId);

            // Create our Comment
            /** @var Comment $comment */
            $comment = Comment::create([
                'content' => $request->get('content'),
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            $response->setContent($comment->toJson());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function delete(Request $request, int $postId, int $commentId): Response
    {
        $response = new Response();

        try {
            // First lets locate the owning post
            $post = $this->getPost($postId);

            // And our Comment record
            $comment = $this->getPostComment($post, $commentId);

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // Disallow delete if the requester is not the comment owner, nor the post owner (allows self moderation of comments on own posts)
            if ($comment->user_id !== $user->id && $post->user_id !== $user->id) {
                throw new UnauthorisedException();
            }

            // Delete our comment
            $comment->delete();

            $response->setContent(Strings::COMMENT_DELETED);

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }

    public function update(Request $request, int $postId, int $commentId): Response
    {
        $response = new Response();

        try {
            $request->validate([
                'content' => ['required', 'string', 'max:65535', 'min:1'],
            ]);

            // First lets locate the owning post
            $post = $this->getPost($postId);

            // And our Comment record
            $comment = $this->getPostComment($post, $commentId);

            // Get our user via the Sanctum token
            /** @var User $user */
            $user = $request->user();

            // And check if our user is the comment owner
            if ($comment->user_id !== $user->id) {
                throw new UnauthorisedException();
            }

            // Update our comment
            $comment->update([
                'content' => $request->get('content')
            ]);

            $response->setContent($comment->toJson());

            return $response;
        } catch (Throwable $throwable) {
            // Pass to our default throwable handler
            return $this->handleThrowableResponse($throwable);
        }
    }
}
