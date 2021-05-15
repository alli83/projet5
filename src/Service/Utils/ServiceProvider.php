<?php

declare(strict_types=1);

namespace  App\Service\Utils;

class ServiceProvider
{

    public function getValidityService(): ValidityService
    {
        return new ValidityService();
    }
    public function getValidateFileService(): ValidateFileService
    {
        return new ValidateFileService();
    }
    public function getTokenService(): TokenService
    {
        return new TokenService();
    }
    public function getPaginationService(): PaginationService
    {
        return new PaginationService();
    }
    public function getMailService(): MailerService
    {
        return new MailerService();
    }
    public function getInformUserService(): InformUserService
    {
        return new InformUserService();
    }
    public function getFileService(): FileService
    {
        return new FileService();
    }
    public function getAuthService(): AuthService
    {
        return new AuthService();
    }
    public function getAuthentificationService(): AuthentificationService
    {
        return new AuthentificationService();
    }
    public function getCreatePostService(): CreatePostService
    {
        return new CreatePostService();
    }
    public function getCheckSignupService(): CheckSignupService
    {
        return new CheckSignupService();
    }
    public function getSetOrderService(): SetOrderService
    {
        return new SetOrderService();
    }
}
