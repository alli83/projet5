
$(document).ready( () => {

    setTimeout( () => {
        $("#flash").remove();
    }, 10000);

    $.validator.addMethod("regexPass", (value) => {
        return /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^&*-]).{10,}$/.test(value);
    });
    $.validator.addMethod("regexPseudo", (value) => {  
        return /^\w{1,20}$/.test(value);
    });
    $.validator.addMethod("regexPostTitle", (value) => {  
        return /^[\wé'"èçàâêîôûäëïöüù:_.(), -?!&,]{1,}$/.test(value);
    });
    $.validator.addMethod("regexText", (value) => {  
        return /^[\\r\\n\wé'"èçàâêîôûäëïöüù:_.(), -?!&,]{1,}$/m.test(value);
    });
    $("#loginForm").validate({

        rules: {
            emailLogin: {
                required: true,
                email: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            passwordLogin: {
                required: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            }
        },
        messages: {
            emailLogin: {
                required: "Vous devez renseigner votre email",
                email: "Le format de votre email est incorrect"
            },
            passwordLogin: {
                required: "Vous devez renseigner votre mot de passe",
            }
        },
        submitHandler: (form) => {
            form.submit();
        }
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
                equalTo: "#password"
            },
            checkSignup: {
                required: true
            }
        },
        messages: {
            pseudoSignup: {
                required: "Vous devez renseigner votre pseudo",
                minlength: "Votre pseudo doit comporter au minimum 3 caractères",
                regexPseudo:" Votre pseudo peut être composé de lettres ou de chiffres (20 maximum) "
            },
            emailSignup: {
                required: "Vous devez renseigner votre email",
                email: "Le format de votre email est incorrect"
            },
            password: {
                required: "Vous devez renseigner un mot de passe",
                minlength: "Le mot de passe doit comporter au moins 10 caractères",
                regexPass: "Votre mot de passe doit comporter au moins 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^&*-"
            },
            passwordConfirm: {
                required: "Vous devez confirmer votre mot de passe",
                equalTo: "les mots de passe ne sont pas identiques"
            },
            checkSignup: {
                required: "Vous devez accepter les conditions d'utilisation"
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
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            lastNameContact: {
                required: true,
                regexPseudo: true,
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
            nameContact: {
                required: "Vous devez renseigner votre prénom",
                regexPseudo: "Votre prénom doit être composé entre 1 et 20 caractères",
            },
            lastNameContact: {
                required: "Vous devez renseigner votre nom",
                regexPseudo: "Votre nom doit être composé entre 1 et 20 caractères",
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
            textComment: {
                required: true,
                maxlength: 600,
                minlength: 2,
                regexText: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            }
        },
        messages: {
            textComment: {
                required: "Merci d'écrire votre commentaire avant de l'envoyer",
                maxlength: "Votre commentaire est trop long",
                minlength: "Un minimum de 2 caractère est requis",
                regexText: "Peut comporter des chiffres et des lettre et: é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
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
                regexPass: "Votre mot de passe doit comporter au moins 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^&*-"
            },
            passwordConfirm2: {
                required: "Vous devez confirmer votre mot de passe",
                minlength: "Le mot de passe doit comporter au moins 10 caractères",
                equalTo: "les mots de passe ne sont pas identiques"
            },
        },
        submitHandler: (form) => {
            form.submit();
        }
    });

        var users = $('#usersToSearch').data('users');
        $("#usersToComplete").autocomplete({
            source: users
          });

          $("#postForm").validate({
            rules: {
                title: {
                    required: true,
                    regexPostTitle: true,
                    maxlength: 255,
                    normalizer: (value) => {
                        return $.trim(value);
                    }
                },
                stand_first: {
                    required: true,
                    regexText: true,
                    maxlength: 1500,
                    normalizer: (value) => {
                        return $.trim(value);
                    }
                },
                usersToComplete: {
                    required: true,
                },
                text: {
                    required: true,
                    regexText: true,
                    maxlength: 10000,
                    normalizer: (value) => {
                        return $.trim(value);
                    }
                },
            },
            messages: {
                title: {
                    required: "Vous devez renseigner un titre",
                    regexPostTitle: "Doit comporter des chiffres et des lettre et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 255 caractères"
                       },
                stand_first: {
                    required: "Vous devez renseigner un chapo",
                    regexText: "Doit comporter des chiffres et des lettre et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 1500 caractères"
                },
                usersToComplete: {
                    required: "Vous devez renseigner un auteur",
                },
                text: {
                    required: "Vous devez renseigner un contenu",
                    regexText: "Doit comporter des chiffres et des lettre et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 10000 caractères"
                },
            },
            submitHandler: (form) => {
                form.submit();
            }
        });
});