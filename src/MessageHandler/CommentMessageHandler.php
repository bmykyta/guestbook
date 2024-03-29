<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SpamChecker            $spamChecker,
        private readonly CommentRepository      $commentRepository,
        private readonly MessageBusInterface    $bus,
        private readonly WorkflowInterface      $workflow,
        private readonly LoggerInterface        $logger
    ) {
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());

        if (!$comment) {
            return;
        }

        if ($this->workflow->can($comment, 'accept')) {
            $score      = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = 'accept';

            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }

            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        } elseif (
            $this->workflow->can($comment, 'publish')
            || $this->workflow->can($comment, 'publish_ham')
        ) {
            $this->workflow->apply(
                $comment,
                $this->workflow->can($comment, 'publish') ? 'publish' : 'publish_ham'
            );
            $this->entityManager->flush();
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', [
                'comment' => $comment->getId(),
                'state'   => $comment->getState(),
            ]);
        }
    }
}