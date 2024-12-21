<?php

declare(strict_types=1);

namespace App\Action\Post;

use App\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class RemovePostCommentsAction
{
    /**
     * @param Post $post
     * @return Post
     */
    public function __invoke(Post $post): Post
    {
        if (count($post->getComments()) === 0) {
            throw new RuntimeException(
                message: 'No comments to delete.',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $post->clearComments(); // Метод для видалення коментарів
        return $post;
    }
}
