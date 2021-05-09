<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Comment;
use App\Model\Repository\CommentRepository;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

class AdminCommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        View $view,
        Session $session,
        ServiceProvider $serviceProvider
    ) {
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function displayAllComments(?array $params = [], ?ParametersBag $request = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            // set pagination
            $offset = $this->serviceProvider->getPaginationService()->setOffset($params);

            // set order
            $order = (!empty($request) && $request->get("order") !== null) ? htmlspecialchars($request->get("order")) : "desc";
            $order = $this->serviceProvider->getValidityService()->isInArray(["asc", "desc"], $order);

            // to determine if it's the last page
            if ($order) {
                $comments = $this->commentRepository->findAll(4, $offset, ['order' => $order["order"]]);
                $end = false;
                if ($comments) {
                    if (!array_key_exists(3, $comments)) {
                        $end = true;
                    }
                    $comments = array_slice($comments, 0, 3);
                }

                //set security token
                $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

                return new Response($this->view->render([
                    'template' => 'listComments',
                    'data' => [
                        'comments' => $comments,
                        'page' => $params === null ? 0 : (int)$params["page"],
                        'filter' => $order,
                        "end" => $end,
                        "tokencsrf" => $tokencsrf
                    ],
                    'env' => 'backoffice'
                ]));
            }
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function validateOneComment(array $params, ?ParametersBag $request): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $this->session->addFlashes("danger", "Une erreur est survenue");
            if ($request !== null) {
                $param = $request->all();

                if (isset($param["text"])) {
                    // check validity security token
                    $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);

                    if ($validToken) {
                        $params["text"] = $param["text"];
                        $validity = $this->serviceProvider->getValidityService();
                        $params = $validity->validityVariables($params);

                        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
                        $text = $comment->getText();

                        if ($this->commentRepository->update($comment)) {
                            $this->serviceProvider->getInformUserService()
                                ->contactUserComment(
                                    $this->session,
                                    $this->userRepository,
                                    $params,
                                    "Commentaire validé",
                                    "frontoffice/mail/publishedComment.html.twig",
                                    $text,
                                    "Le commentaire est validé et la confirmation envoyée"
                                );
                        }
                    }
                }
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function deleteOneComment(array $params, ?ParametersBag $request): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($request !== null) {
                $param = $request->all();

                if (isset($param["text"])) {
                    // check validity security token
                    $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);

                    if ($validToken) {
                        $params["text"] = $param["text"];
                        $validity = $this->serviceProvider->getValidityService();
                        $params = $validity->validityVariables($params);

                        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
                        $text = $comment->getText();

                        if ($this->commentRepository->delete($comment)) {
                            $this->serviceProvider->getInformUserService()
                                ->contactUserComment(
                                    $this->session,
                                    $this->userRepository,
                                    $params,
                                    "Commentaire supprimé",
                                    "frontoffice/mail/deletedComment.html.twig",
                                    $text,
                                    "Le commentaire à bien été supprimé et la confirmation envoyée"
                                );
                        }
                    }
                }
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }
}
