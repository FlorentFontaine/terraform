/* Regex for email verification ****************/
var regexMail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

// IE detection
var ua = window.navigator.userAgent;
var old_ie = ua.indexOf('MSIE ');
var new_ie = ua.indexOf('Trident/');

var token = "";
var expiredAt = "";

/* JS called on login page  ********************/
var login = {
    init: function() {
        // IE CSS
        if ((old_ie > -1) || (new_ie > -1)) {
            $("#form-container, #welcome-container").css("transform", "translate(0, 100%)");
            $("body").css("overflow", "hidden");
        }

        // Submit login form
        $("#submitLogin").click(function(e) {
            $(this).attr("disabled", "disabled");
            e.preventDefault();
            if(login.checkForm()) {
                login.submitForm();
            }
            $(this).removeAttr("disabled");
        });

        $("#checkPassword").click(function () {
            var txtPassword = $("#password");
            if (txtPassword.is(':password')) {
                txtPassword.attr('type', 'text');
                $("#icon-password").removeClass("fa-eye");
                $("#icon-password").addClass("fa-eye-slash");
            } else {
                txtPassword.attr('type', 'password');
                $("#icon-password").removeClass("fa-eye-slash");
                $("#icon-password").addClass("fa-eye");
            }
        });
    },

    checkForm: function() {
        var email = $("#email").val();
        var password = $("#password").val();

        // Reset all errors
        $("#email, #password").removeClass("is-invalid");

        // Check if complete
        if(email.trim() == "") {
            $("#email-error").html("Le champ Adresse mail est incomplet");
            $("#email").addClass("is-invalid");
        }
        if(password.trim() == "") {
            $("#password-error").html("Le champ Mot de passe est incomplet");
            $("#password").addClass("is-invalid");
            return false;
        }

        // Check email validation
        if(!regexMail.test(email)) {
            $("#email-error").html("Le champ Adresse mail n'est pas une adresse mail valide");
            $("#email").addClass("is-invalid");
            return false;
        }

        return true;
    },

    submitForm: function() {
        // Ajax call to the authentification server api
        $.ajax({
            url: "../auth/authentification.ajax.php",
            type: "POST",
            data: {
                action: "login",
                email: $("#email").val(),
                password: $("#password").val()
            },
            success: function(response) {
                $("#submitLogin").removeAttr("disabled");
                // response : [ data: [], info: [ code: xxx, message: str ], success: true ]
                if(response && response.success) {
                    token = response.data.accessToken;
                    expiredAt = response.data.expiredAt;
                    var isAdmin = response.data.admin;

                    // Get all files user doesn't accept yet
                    var files = response.data.files.filter(function(file) {
                        return file.acceptedAt === null;
                    });

                    if(Array.isArray(files) && files.length > 0 && isAdmin == 0) {
                        $("#form-container").hide();
                        files.forEach(function(file) {
                            var embed = $("<embed>");
                            embed.attr({
                                src: file.url,
                                type: "application/pdf",
                                width: "100%"
                            }).css("height", "60vh");

                            $("#file-container > #embed").append(embed);

                            // IE CSS
                            if ((old_ie > -1) || (new_ie > -1)) {
                                $("#file-container").css("transform", "translate(0, 15%)");
                            }

                            $("#file-container").show();

                            // Accept File
                            $("#acceptFile").click({ fileId: file.id, token: token }, function(event) {
                                event.preventDefault();
                                login.acceptFile(event.data.fileId, event.data.token);
                            });

                            // Decline File
                            $("#declineFile").click(function(event) {
                                event.preventDefault();
                                $("#file-container > #embed > *").remove();
                                $("#file-container").hide();
                                $("#form-container").show();
                                return;
                            });
                        });
                    } else {
                        window.location.href = "../login/login.php?fromLoginPage=1";
                    }
                } else {
                    $("#email, #password").addClass("is-invalid");
                    $("#email-error").html("Une erreur est survenue.");
                }
            },
            error: function(response) {
                $("#submitLogin").removeAttr("disabled");
                // response : [ errors: [], info: [ code: xxx, message: str ], success: false ]
                if (response.status === 429) {
                    $("#email, #password").addClass("is-invalid");
                    $("#email-error").html("Tentatives de connexions trop nombreuses. Merci de patienter quelques minutes.");
                } else if (!response.success) {
                    $("#email, #password").addClass("is-invalid");
                    $("#email-error").html("Identification incorrecte.");
                }
            }
        });
    },

    acceptFile: function(fileId, token) {
        $.ajax({
            url: "../auth/authentification.ajax.php",
            type: "POST",
            data: {
                action: "acceptFile",
                fileId: fileId,
                token: token
            },
            success: function(response) {
                if(response && response.success) {
                    window.location.href = "../login/login.php?token=" + token + "&expiredAt=" + expiredAt;
                }
            }
        });
    }
};

/* JS called on mail-password page *************/
var mail_password = {
    init: function() {
        // IE CSS
        if ((old_ie > -1) || (new_ie > -1)) {
            $("#form-container, #welcome-container").css("transform", "translate(0, 100%)");
            $("body").css("overflow", "hidden");
        }

        // Submit mail password form
        $("#submitMailPassword").click(function(e) {
            e.preventDefault();
            if(mail_password.checkForm()) {
                mail_password.submitForm();
            }
        });
    },

    checkForm: function() {
        var email = $("#email").val();

        // Reset all errors
        $("#email").removeClass("is-invalid");

        // Check if complete
        if(email.trim() == "") {
            $("#email-error").html("Le champ Adresse mail est incomplet");
            $("#email").addClass("is-invalid");
            return false;
        }

        // Check email validation
        if(!regexMail.test(email)) {
            $("#email-error").html("Le champ Adresse mail n'est pas une adresse mail valide");
            $("#email").addClass("is-invalid");
            return false;
        }

        return true;
    },

    submitForm: function() {
        // Ajax call to the authentification server api
        $.ajax({
            url: "../auth/authentification.ajax.php",
            type: "POST",
            data: {
                action: "mailPassword",
                email: $("#email").val()
            },
            success: function(response) {
                if(response && response.success) {
                    $("#email-success").html(response.info.message).css("display", "block");
                    $("#email").addClass("is-valid");
                    setTimeout(function() {
                        $("#email").removeClass("is-valid");
                        $("#email-success").html("").css("display", "none");
                        window.location.href = "../";
                    }, 3000);
                }
            },
            error: function() {
                $("#email").addClass("is-invalid");
                $("#email-error").html("Une erreur est survenue. Contactez le support CICD.").css("display", "block");
            }
        });
    }
};

/*JS called on reset-paswword page  ************/
var reset_password = {
    init: function() {
        // IE CSS
        if ((old_ie > -1) || (new_ie > -1)) {
            $("#form-container, #welcome-container").css("transform", "translate(0, 100%)");
            $("body").css("overflow", "hidden");
        }

        // Submit password form
        $("#submitResetPassword").click(function(e) {
            e.preventDefault();
            if(reset_password.checkForm() && reset_password.checkPassword()) {
                reset_password.submitForm();
            }
        });

        $("#checkPassword").click(function () {
            var txtPassword = $("#password");
            if (txtPassword.is(':password')) {
                txtPassword.attr('type', 'text');
                $("#icon-password").removeClass("fa-eye");
                $("#icon-password").addClass("fa-eye-slash");
            } else {
                txtPassword.attr('type', 'password');
                $("#icon-password").removeClass("fa-eye-slash");
                $("#icon-password").addClass("fa-eye");
            }
        });

        $("#checkConfirmPassword").click(function () {
            var txtPassword = $("#password_confirm");
            if (txtPassword.is(':password')) {
                txtPassword.attr('type', 'text');
                $("#icon-confirm-password").removeClass("fa-eye");
                $("#icon-confirm-password").addClass("fa-eye-slash");
            } else {
                txtPassword.attr('type', 'password');
                $("#icon-confirm-password").removeClass("fa-eye-slash");
                $("#icon-confirm-password").addClass("fa-eye");
            }
        });
    },

    checkPassword: function () {
        var regex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[?!@#\$%\^&\*.\(\)+\/])(?=.{8,})");
        var password = $("#password").val();

        if (!regex.test(password)) {
            $("#password").addClass("is-invalid");
            $("#password-error").html("Vous devez respecter la politique de s&eacute;curit&eacute; de mot de passe");
            $("#password_confirm").addClass("is-invalid");
            return false;
        }

        return true;
    },

    checkForm: function() {
        var password = $("#password").val();
        var passwordConfirm = $("#password_confirm").val();

        // Reset all errors
        $("#password").removeClass("is-invalid");
        $("#password_confirm").removeClass("is-invalid");

        // Check if complete
        if(password.trim() == "") {
            $("#password-error").html("Le champ Mot de passe est incomplet");
            $("#password").addClass("is-invalid");
            return false;
        }

        // Check if match
        if(password.trim() !== passwordConfirm.trim()) {
            $("#password-error").html("Le champ Mot de passe est incomplet");
            $("#password").addClass("is-invalid");
            return false;
        }

        return true;
    },

    submitForm: function() {
        // Ajax call to the authentification server api
        $.ajax({
            url: "../auth/authentification.ajax.php",
            type: "POST",
            data: {
                action: "resetPassword",
                password: $("#password").val(),
                token: $.urlParam("token")
            },
            success: function(response) {
                if(response && response.success) {
                    $("#password-success").html(response.info.message);
                    $("#password").addClass("is-valid");
                    setTimeout(function() {
                        $("#password").removeClass("is-valid");
                        $("#password-success").html("");
                        window.location.href = "../";
                    }, 3000);
                }
            },
            error: function() {
                $("#password").addClass("is-invalid");
                $("#password-error").html("Une erreur est survenue. Contactez le support CICD.").css("display", "block");
            }
        });
    }
};

// Function to get URL params
$.urlParam = function(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if(results == null) {
        return null;
    } else {
        return decodeURI(results[1]) || 0;
    }
};