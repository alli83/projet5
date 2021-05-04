
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
            pseudoSignup: {
                required: true,
                minlength: 3,
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            emailSignup: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            password: {
                required: true,
                minlength: 10,
                regexPass: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            passwordConfirm: {
                required: true,
                minlength: 10,
                equalTo: "#password"
            },
            check: {
                required: true
            }
        },
        messages: {
            pseudoSignup: {
                required: "Vous devez renseigner votre pseudo",
                minlength: "Votre pseudo doit comporter au minimum 3 caractères"
            },
            emailSignup: {
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
            nameContact: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            lastNameContact: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            emailContact: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            messageContact: {
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
            lastNameContact: {
                required: "Vous devez renseigner votre nom"
            },
            emailContact: {
                required: "Vous devez renseigner un email",
                email: "L'email est invalide",
            },
            messageContact: {
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
            pseudoComment: {
                required: true,
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            textComment: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            }
        },
        messages: {
            pseudoComment: {
                required: "Vous devez renseigner un pseudo"
            },
            textComment: {
                required: "Votre commentaire"
            },
        },
        submitHandler: (form) => {
            form.submit();
        }
    });
    $("#newPass").validate({

        rules: {
            emailReset: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
        },
        messages: {
            emailReset: {
                required: "Vous devez renseigner un email",
                email: "L'email est invalide",
            },
        },
        submitHandler: (form) => {
            form.submit();
        }
    });

    $("#confirmReset").validate({

        rules: {
            password2: {
                required: true,
                minlength: 10,
                regexPass: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            passwordConfirm2: {
                required: true,
                minlength: 10,
                equalTo: "#password2"
            },
        },
        messages: {
            password2: {
                required: "Vous devez renseigner un mot de passe",
                minlength: "Le mot de passe doit comporter au moins 10 caractères",
                regexPass: "Votre mot de passe doit comporter 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^&*-"
            },
            passwordConfirm2: {
                required: "Vous devez confirmer votre mot de passe",
                equalTo: "les mots de passe ne sont pas identiques"
            },
        },
        submitHandler: (form) => {
            form.submit();
        }
    });
});