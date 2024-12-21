<?php

declare(strict_types=1);

namespace App\Action\Post;

use App\Entity\Post;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SetPostPublishedAction
{
    /**
     * @param Post $post
     * @return Post
     */
    public function __invoke(Post $post): Post
    {
        if ($post->getStatus() === 'published') {
            throw new RuntimeException(
                message: 'This post is already published.',
                code: Response::HTTP_BAD_REQUEST
            );
        }

        $post->setStatus('published');
        return $post;
    }
}
