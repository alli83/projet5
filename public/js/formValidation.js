
$(document).ready( () => {

    setTimeout( () => {
        $("#flash").remove();
    }, 10000);

    $.validator.addMethod("regexPass", (value) => {
        return /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^*-]).{10,}$/.test(value);
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
    $.validator.addMethod("regexAuthor", (value) => {  
        return /^[ \w,.@]{1,}$/m.test(value);
    });
    $.validator.addMethod("regexEmail", (value) => {  
        return /^[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+$/m.test(value);
    });
    $("#loginForm").validate({

        rules: {
            emailLogin: {
                required: true,
                email: true,
                regexEmail: true,
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
                email: "Le format de votre email est incorrect",
                regexEmail: "Vous devez renseigner un email valide"
            },
            passwordLogin: {
                required: "Vous devez renseigner votre mot de passe"
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
                minlength: 1,
                regexPseudo: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            emailSignup: {
                required: true,
                email: true,
                regexEmail: true,
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
                minlength: "Votre pseudo doit comporter au minimum 1 caractère",
                regexPseudo:" Votre pseudo peut être composé de lettres ou de chiffres (20 maximum) "
            },
            emailSignup: {
                required: "Vous devez renseigner votre email",
                email: "Le format de votre email est incorrect",
                regexEmail: "Vous devez renseigner un email valide"
            },
            password: {
                required: "Vous devez renseigner un mot de passe",
                minlength: "Le mot de passe doit comporter au moins 10 caractères",
                regexPass: "Votre mot de passe doit comporter au moins 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^*-"
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
                regexEmail: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
            messageContact: {
                required: true,
                regexText:true,
                maxlength:1000,
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
                regexPseudo: "Votre prénom doit être composé entre 1 et 20 caractères (chiffres ou lettres)"
            },
            lastNameContact: {
                required: "Vous devez renseigner votre nom",
                regexPseudo: "Votre nom doit être composé entre 1 et 20 caractères (chiffres ou lettres)"
            },
            emailContact: {
                required: "Vous devez renseigner un email",
                email: "L'email est invalide",
                regexEmail: "Vous devez renseigner un email valide"
            },
            messageContact: {
                required: "Veuillez compléter ce champs",
                maxlength:"Votre message dépasse les 1000 caractères",
                regexText:"Peut comporter des chiffres et des lettres et: é\'\"èçàâêîôûäëïöüù:_.(), -?!&,"
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
                maxlength: "Votre commentaire est trop long (600 caractères maximum)",
                minlength: "Un minimum de 2 caractère est requis",
                regexText: "Peut comporter des chiffres et des lettres et: é\'\"èçàâêîôûäëïöüù:_.(), -?!&,"
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
                regexEmail: true,
                normalizer: (value) => {
                    return $.trim(value);
                }
            },
        },
        messages: {
            emailReset: {
                required: "Vous devez renseigner un email",
                email: "L'email est invalide",
                regexEmail: "Vous devez renseigner un email valide"
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
                regexPass: "Votre mot de passe doit comporter au moins 10 caractères, une majuscule, un chiffre et un caractère spécial #?!@%^*-"
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

        var users = $("#usersToSearch").data("users");
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
                    regexAuthor:true,
                    maxlength: 500,
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
                    regexPostTitle: "Doit comporter des chiffres et des lettres et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 255 caractères"
                       },
                stand_first: {
                    required: "Vous devez renseigner un chapo",
                    regexText: "Doit comporter des chiffres et des lettres et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 1500 caractères"
                },
                usersToComplete: {
                    required: "Vous devez renseigner un auteur",
                    regexAuthor: "Merci de sélectionner parmi la liste. Ce champs peut comporter lettres, chiffres et @",
                    maxlength: "La limite est à 500 caractères"
                },
                text: {
                    required: "Vous devez renseigner un contenu",
                    regexText: "Doit comporter des chiffres et des lettres et peut comporter é\'\"èçàâêîôûäëïöüù:_.(), -?!&,",
                    maxlength: "La limite est à 10000 caractères"
                },
            },
            submitHandler: (form) => {
                form.submit();
            }
        });
});