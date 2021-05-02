
$(document).ready(function () {

    setTimeout(function () {
        $("#flash").remove();
    }, 10000);

    $.validator.addMethod("regexPass", function (value) {
        return /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^&*-]).{10,}$/.test(value);
    });
    $.validator.addMethod("regexPseudo", function (value) {
        return /^\w{3,15}$/.test(value);
    });


    $("#registration-form").validate({

        rules: {
            pseudo: {
                required: true,
                minlength: 3,
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            email: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            password: {
                required: true,
                minlength: 8,
                regexPass: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            passwordConfirm: {
                required: true,
                minlength: 8,
                equalTo: "#password"
            },
            check: {
                required: true
            }
        },
        messages: {
            pseudo: {
                required: "Vous devez renseigner votre pseudo",
                minlength: "Votre pseudo doit comporter au minimum 3 caractères"
            },
            email: {
                required: "Vous devez renseigner votre email",
                email: "Le format de votre email est incorrect"
            },
            password: {
                required: "Vous devez renseigner un mot de passe",
                minlength: "Le mot de passe doit comporter au moins 10 caractères",
                regexPass: "Votre mot de passe doit comporter 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^&*-"
            },
            passwordConfirm: {
                required: "Vous devez confirmer votre mot de passe",
                equalTo: "les mots de passe ne sont pas identiques"
            },
            check: {
                required: "Vous devez accepter les conditions"
            }

        },
        submitHandler: (form) => {
            form.submit();
        }
    });

    $("#contactForm").validate({
        rules: {
            name: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            lastName: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            email: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            message: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            check: {
                required: true,
            }
        },
        messages: {
            name: {
                required: "Vous devez renseigner votre prénom"
            },
            lastName: {
                required: "Vous devez renseigner votre nom"
            },
            email: {
                required: "Vous devez renseigner un email",
                email: "L'email est invalide",
            },
            message: {
                required: "Veuillez compléter ce champs"
            },
            check: {
                required: "Vous devez accepter les conditions"
            }
        },
        submitHandler: (form) => {
            form.submit();
        }
    });

    $("#formComment").validate({

        rules: {
            pseudo: {
                required: true,
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            text: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            }
        },
        messages: {
            pseudo: {
                required: "Vous devez renseigner un pseudo"
            },
            text: {
                required: "Votre commentaire"
            },
        },
        submitHandler: (form) => {
            form.submit();
        }
    });
});