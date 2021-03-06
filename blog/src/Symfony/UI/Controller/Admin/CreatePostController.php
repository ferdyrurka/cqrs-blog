<?php

declare(strict_types=1);

namespace App\Symfony\UI\Controller\Admin;

use App\Blog\Application\UseCase\Command\Post\CreatePostCommand;
use App\Blog\Domain\Post\Exception\FoundException;
use App\Symfony\UI\Form\Admin\CreatePostForm;
use App\Symfony\UI\Request\DTO\CreatePostDTO;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

class CreatePostController extends AbstractController
{
    /**
     * @Route("/admin/create/post", methods={"POST"}, host="%admin_host%")
     */
    public function createAction(Request $request): JsonResponse
    {
        $form = $this->createForm(CreatePostForm::class, new CreatePostDTO());
        $form->submit(
            json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getData();

            try {
                $this->dispatchMessage(new CreatePostCommand(
                    $dto->title,
                    $dto->content,
                    $dto->publishType,
                    new Carbon($dto->plannedPublishAt),
                    $dto->customSlug
                ));
            } catch (HandlerFailedException $exception) {
                if ($exception->getPrevious() instanceof FoundException) {
                    return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_FOUND);
                }

                throw $exception;
            }

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['valid' => (string) $form->getErrors()], Response::HTTP_BAD_REQUEST);
    }
}
