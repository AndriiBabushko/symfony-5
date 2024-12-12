<?php

namespace App\Services;

use App\Entity\Interaction;
use App\Entity\User;
use App\Repository\InteractionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InteractionService
{
    private EntityManagerInterface $entityManager;
    private InteractionRepository $interactionRepository;
    private UserRepository $userRepository;
    private RequestCheckerService $requestCheckerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        InteractionRepository $interactionRepository,
        UserRepository $userRepository,
        RequestCheckerService $requestCheckerService
    ) {
        $this->entityManager = $entityManager;
        $this->interactionRepository = $interactionRepository;
        $this->userRepository = $userRepository;
        $this->requestCheckerService = $requestCheckerService;
    }

    public function getAllInteractions(): array
    {
        $interactions = $this->interactionRepository->findAll();
        return array_map([$this, 'serializeInteraction'], $interactions);
    }

    public function getInteractionById(int $id): array
    {
        $interaction = $this->interactionRepository->find($id);

        if (!$interaction) {
            throw new NotFoundHttpException('Interaction not found');
        }

        return $this->serializeInteraction($interaction);
    }

    public function createInteraction(array $data): array
    {
        $this->requestCheckerService->validateRequestDataByConstraints($data);

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $interaction = $this->createInteractionObject($user, $data['interactionType'], $data['targetId']);
        $this->entityManager->persist($interaction);
        $this->entityManager->flush();

        return $this->serializeInteraction($interaction);
    }

    public function updateInteraction(int $id, array $data): array
    {
        $interaction = $this->interactionRepository->find($id);

        if (!$interaction) {
            throw new NotFoundHttpException('Interaction not found');
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst(strtolower($key));
            if (method_exists($interaction, $method)) {
                $interaction->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($interaction);

        $this->entityManager->flush();

        return $this->serializeInteraction($interaction);
    }

    public function deleteInteraction(int $id): void
    {
        $interaction = $this->interactionRepository->find($id);

        if (!$interaction) {
            throw new NotFoundHttpException('Interaction not found');
        }

        $this->entityManager->remove($interaction);
        $this->entityManager->flush();
    }

    private function createInteractionObject(User $user, string $interactionType, ?int $targetId): Interaction
    {
        $interaction = new Interaction();
        $interaction->setUser($user);
        $interaction->setInteractionType($interactionType);
        $interaction->setTargetId($targetId);
        $interaction->setCreatedAt(new \DateTime());

        return $interaction;
    }

    private function serializeInteraction(Interaction $interaction): array
    {
        return [
            'id' => $interaction->getId(),
            'interactionType' => $interaction->getInteractionType(),
            'userId' => $interaction->getUser()->getId(),
            'targetId' => $interaction->getTargetId(),
            'createdAt' => $interaction->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
