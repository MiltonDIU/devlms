var isCameraAllowed = false;

define(['jquery', 'core/ajax', 'core/notification'],
    function ($, Ajax, Notification) {

        $(function () {
            $('#id_submitbutton').prop("disabled", true);
            $('#id_proctoring').on('change', function () {
                if (this.checked && isCameraAllowed) {
                    $('#id_submitbutton').prop("disabled", false);
                } else {
                    $('#id_submitbutton').prop("disabled", true);
                }
            });
            $('.aalink').on('click', function () {
                // alert('kamrul');
                window.obx.proctoringEnd();
            });
            $('#id_submitbutton').on('click', function () {
                // alert('kamrul');
                // function closeWindow() {
                //     window.ob.closeA();
                    window.ob.proctoringStart();
                // }
                // setTimeout(function (){
                //     closeWindow();
                // }, 2000);
            });
        });

        /**
         * Function hideButtons
         */
        function hideButtons() {
            $('.mod_quiz-next-nav').prop("disabled", true);
            $('.submitbtns').html('<p class="text text-red red">You need to enable web camera before submitting this quiz!</p>');
        }
        function hideButtonsScheen() {
            $('.mod_quiz-next-nav').prop("disabled", true);
            $('.submitbtns').html('<p class="text text-red red">You need to enable web Screen Share before submitting this quiz!</p>');
        }

        var firstcalldelay = 3000; // 3 seconds after the page load
        var takepicturedelay = 120000; // 120 seconds
        var takescheendelay = 120000;  // 120 seconds

        return {


            setup: function (props) {

                // document.addEventListener('contextmenu', event => event.preventDefault());
                document.addEventListener('contextmenu', function(e) {
                    if (!e.target.matches('textarea')) {
                        e.preventDefault();
                        takeworningright('Right Click pressed');
                        showKamrulToaster('Right Click pressed');
                    }
                }, false);

                $(document).keydown(function (event) {
                    if (event.keyCode == 123) { // Prevent F12
                        takeworningright('F12 pressed');
                        showKamrulToaster('F12 pressed');
                        return false;
                    } else if (event.ctrlKey && event.shiftKey && event.keyCode === 73) { // Prevent Ctrl+Shift+I
                        takeworningright('Ctrl+Shift+I pressed');
                        showKamrulToaster('Ctrl+Shift+I pressed');
                        return false;
                    } else if (event.ctrlKey && event.keyCode === 83) { // Prevent Ctrl+S
                        takeworningright('Save pressed');
                        showKamrulToaster('Save pressed');
                        return false;
                    } else if (event.ctrlKey && event.keyCode === 67) { // Prevent Ctrl+C
                        takeworningright('Copy Warning');
                        showKamrulToaster('Copy Warning');
                        return false;
                    } else if (event.ctrlKey && event.keyCode === 86) { // Prevent Ctrl+V
                        takeworningright('Paste Warning');
                        showKamrulToaster('Paste Warning');
                        // return false;
                    } else if (event.ctrlKey && event.keyCode === 88) { // Prevent Ctrl+X
                        takeworningright('Cut Warning');
                        showKamrulToaster('Cut Warning');
                        return false;
                    } else if (event.ctrlKey && event.keyCode === 85) { // Prevent Ctrl+U
                        takeworningright('Ctrl+U pressed');
                        showKamrulToaster('Ctrl+U pressed');
                        return false;
                    }
                });
                // document.onkeydown = function(e) {
                //     if (e.ctrlKey && (e.keyCode === 83)) {
                //         takeworningright('New Warning 83');
                //         showKamrulToaster('New Warning');
                //         return false;
                //     } else {
                //         return true;
                //     }
                // };
                // e.keyCode === 67 ||
                // e.keyCode === 86 ||
                // e.keyCode === 83 ||
                // e.keyCode === 85 ||
                // e.keyCode === 117
                document.onkeydown = function(e) {
                    if (e.ctrlKey &&
                        (e.keyCode === 117)) {
                        takeworningright('Other Warning ');
                        showKamrulToaster('Other Warning');
                        return false;
                    } else {
                        return true;
                    }
                };
                $(document).keypress("u", function(e) {
                    if (e.ctrlKey) {
                        takeworningright('Ctrl+U pressed');
                        showKamrulToaster('Ctrl+U pressed');
                        return false;
                    } else {
                        return true;
                    }
                });
                window.top.onblur = function() {
                    takeworningright('New tab opened');
                    showKamrulToaster('New tab opened');
                    // takescheenpicture();
                    setTimeout(takescheenpicture, 1000);
                    setTimeout(takescheenpicture, 15000);
                };
                // var isClicked = false;
                // $(window).on('beforeunload', function(){
                //     //  Was the <a> tag clicked?
                //     if(isClicked)
                //     {
                //         //  Reset isClicked
                //         isClicked = false;
                //         //  Display the message
                //         showKamrulToaster('You are leaving the site.');
                //         // alert 'You are leaving the site.';
                //     }
                // });
                // 'use strict';
                // if (adapter.browserDetails.browser == 'firefox') {
                //     adapter.browserShim.shimGetDisplayMedia(window, 'screen');
                // }
                // alert('kamrul');

                // function handleSuccess(stream) {
                //     const video = document.querySelector('#gum-local');
                //     video.srcObject = stream;
                //
                //     // demonstrates how to detect that the user has stopped
                //     // sharing the screen via the browser UI.
                //     stream.getVideoTracks()[0].addEventListener('ended', () => {
                //         errorMsg('The user has ended sharing the screen');
                //     });
                // }

                // function handleError(error) {
                //     errorMsg(`getDisplayMedia error: ${error.name}`, error);
                // }
                //
                function errorMsg(msg) {
                    // const errorElement = document.querySelector('#errorMsg');
                    // errorElement.innerHTML += `<p>${msg}</p>`;
                    // if (typeof error !== 'undefined') {
                    //     console.error(error);
                    // }
                }

                // if (adapter.browserDetails.browser == 'firefox') {
                //     adapter.browserShim.shimGetDisplayMedia(window, 'screen');
                // }


                function showKamrulToaster(msg, duration) {
                    if (duration == undefined) {
                        duration = 2000;
                    }
                    var toast = $("<div class='disabler-toaster toaster-container'>" +
                        "<lable class='toaster-message'>" + msg + "</lable>" +
                        "</div>");
                    $('html').append(toast);
                    $(toast).addClass('show');
                    setTimeout(function () {
                        $(toast).addClass('fade');
                        setTimeout(function () {
                            $(toast).removeClass('fade');
                            setTimeout(function () {
                                $(toast).remove();
                            }, 300);
                        }, duration);
                    });
                }

                // Skip for summary page
                if (document.getElementById("page-mod-quiz-summary") !== null &&
                    document.getElementById("page-mod-quiz-summary").innerHTML.length) {
                    return false;
                }
                if (document.getElementById("page-mod-quiz-review") !== null &&
                    document.getElementById("page-mod-quiz-review").innerHTML.length) {
                    return false;
                }

                var width = 230; // We will scale the photo width to this
                var height = 0; // This will be computed based on the input stream
                var scheenwidth = 230; // We will scale the photo width to this
                var scheenheight = 0; // This will be computed based on the input stream
                var streaming = false;
                var scheenstreaming = false;
                var data = null;
                var scheendata = null;

                $('#mod_quiz_navblock').append('<div class="card-body p-3"><h3 class="no text-left">Webcam</h3> <br/>'
                    + '<video id="video">Video stream not available.</video><canvas id="canvas" style="display:none;"></canvas>'
                    + '<div class="output" style="display:none;">'
                    + '<img id="photo" alt="The picture will appear in this box."/></div></div>');

                $('#mod_quiz_navblock').append('<div class="card-body p-3"><h3 class="no text-left">Share Screen</h3> <br/>'
                    + '<video id="gum-local" class="" autoplay playsinline muted >Screen screen not available.</video>'
                    + '<canvas id="scheencanvas" style="display:none;"></canvas>'
                    + '<div class="output" style="display:none;">'
                    + '<img id="scheenphoto" alt="The picture will appear in this box."/></div></div>');

                var video = document.getElementById('video');
                var canvas = document.getElementById('canvas');
                var photo = document.getElementById('photo');

                var scheenvideo = document.getElementById('gum-local');
                var scheencanvas = document.getElementById('scheencanvas');
                var scheenphoto = document.getElementById('scheenphoto');

                var clearphoto = function() {
                    var context = canvas.getContext('2d');
                    context.fillStyle = "#AAA";
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    data = canvas.toDataURL('image/png');
                    photo.setAttribute('src', data);
                };

                var takeworningright = function($datap) {

                    // var context = canvas.getContext('2d');
                    // // if (width && height) {
                    //     canvas.width = width;
                    //     canvas.height = height;
                    //     context.drawImage(video, 0, 0, width, height);
                    //     data = canvas.toDataURL('image/png');
                    //     photo.setAttribute('src', data);
                    //     props.webcampicture = data;

                        var wsfunction = 'quizaccess_proctoring_send_rightworning';
                        var params = {
                            'courseid': props.courseid,
                            'screenshotid': props.id,
                            'quizid': props.quizid,
                            'webcampicture': $datap,
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        Ajax.call([request])[0].done(function(data) {
                            if (data.warnings.length < 1) {
                                // NO; pictureCounter++;
                            } else {
                                // if (video) {
                                    Notification.addNotification({
                                        message: 'Something went wrong during taking the image.',
                                        type: 'error'
                                    });
                                // }
                            }
                        }).fail(Notification.exception);
                    // } else {
                    //     clearphoto();
                    // }
                };
                var takepicture = function() {
                    var context = canvas.getContext('2d');
                    if (width && height) {
                        canvas.width = width;
                        canvas.height = height;
                        context.drawImage(video, 0, 0, width, height);
                        data = canvas.toDataURL('image/png');
                        photo.setAttribute('src', data);
                        props.webcampicture = data;

                        var wsfunction = 'quizaccess_proctoring_send_camshot';
                        var params = {
                            'courseid': props.courseid,
                            'screenshotid': props.id,
                            'quizid': props.quizid,
                            'webcampicture': data,
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        Ajax.call([request])[0].done(function(data) {
                            if (data.warnings.length < 1) {
                                // NO; pictureCounter++;
                            } else {
                                if (video) {
                                    Notification.addNotification({
                                        message: 'Something went wrong during taking the image.',
                                        type: 'error'
                                    });
                                }
                            }
                        }).fail(Notification.exception);
                    } else {
                        clearphoto();
                    }
                };
                var takescheenpicture = function() {
                    var scheencontext = scheencanvas.getContext('2d');
                    if (width && height) {
                        scheencanvas.width = width;
                        scheencanvas.height = height;
                        scheencontext.drawImage(scheenvideo, 0, 0, width, height);
                        scheendata = scheencanvas.toDataURL('image/png');
                        scheenphoto.setAttribute('src', scheendata);
                        props.webcampicture = scheendata;

                        var wsfunction = 'quizaccess_proctoring_send_scheen';
                        var params = {
                            'courseid': props.courseid,
                            'screenshotid': props.id,
                            'quizid': props.quizid,
                            'webcampicture': scheendata,
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        Ajax.call([request])[0].done(function(scheendata) {
                            if (scheendata.warnings.length < 1) {
                                // NO; pictureCounter++;
                            } else {
                                if (scheenvideo) {
                                    Notification.addNotification({
                                        message: 'Something went wrong during taking the image.',
                                        type: 'error'
                                    });
                                }
                            }
                        }).fail(Notification.exception);
                    } else {
                        clearphoto();
                    }
                };
                // let supportedConstraints = navigator.mediaDevices.getSupportedConstraints();
                // let displayMediaOptions = {
                //     video: {
                //     },
                //     audio: false;
                // };
                // if (supportedConstraints.displaySurface) {
                //     displayMediaOptions.video.displaySurface = "monitor";
                // }
                var displayMediaStreamConstraints = {
                    video: {cursor:"never"}
                };
                if (navigator.mediaDevices.getDisplayMedia) {
                navigator.mediaDevices.getDisplayMedia(displayMediaStreamConstraints)
                    .then(function (stream) {
                        // console.log(stream);
                        const scheenvideo = document.querySelector('#gum-local');
                        scheenvideo.srcObject = stream;
                        scheenvideo.play();
                        stream.getVideoTracks()[0].addEventListener('ended', () => {
                            errorMsg('The user has ended sharing the screen');
                        });
                        // isCameraAllowed = true;
                        // return;
                    }).catch(function () {

                    hideButtonsScheen();
                });
                } else {
                    navigator.getDisplayMedia(displayMediaStreamConstraints).then(function (stream) {
                        // console.log(stream);
                        const scheenvideo = document.querySelector('#gum-local');
                        scheenvideo.srcObject = stream;
                        scheenvideo.play();
                        stream.getVideoTracks()[0].addEventListener('ended', () => {
                            errorMsg('The user has ended sharing the screen');
                        });
                        // isCameraAllowed = true;
                        // return;
                    }).catch(function () {

                        hideButtonsScheen();
                    });
                }

                if (scheenvideo) {
                    scheenvideo.addEventListener('canplay', function() {
                        if (!scheenstreaming) {
                            scheenheight = scheenvideo.videoHeight / (scheenvideo.videoWidth / scheenwidth);
                            // Firefox currently has a bug where the height can't be read from
                            // The video, so we will make assumptions if this happens.
                            if (isNaN(scheenheight)) {
                                scheenheight = scheenwidth / (4 / 3);
                            }
                            scheenvideo.setAttribute('width', scheenwidth);
                            scheenvideo.setAttribute('height', scheenheight);
                            scheencanvas.setAttribute('width', scheenwidth);
                            scheencanvas.setAttribute('height', scheenheight);
                            scheenstreaming = true;
                        }
                    }, false);

                    // Allow to click picture
                    scheenvideo.addEventListener('click', function (ev) {
                        // takescheenpicture();
                        ev.preventDefault();
                    }, false);
                    // setTimeout(takescheenpicture, firstcalldelay);
                    // setInterval(takescheenpicture, takescheendelay);

                } else {
                    hideButtonsScheen();
                }

                navigator.mediaDevices.getUserMedia({video: true, audio: false})
                    .then(function(stream) {
                        video.srcObject = stream;
                        video.play();
                        isCameraAllowed = true;
                        return;
                    })
                    .catch(function() {
                        hideButtons();
                    });

                if (video) {
                    video.addEventListener('canplay', function() {
                        if (!streaming) {
                            height = video.videoHeight / (video.videoWidth / width);
                            // Firefox currently has a bug where the height can't be read from
                            // The video, so we will make assumptions if this happens.
                            if (isNaN(height)) {
                                height = width / (4 / 3);
                            }
                            video.setAttribute('width', width);
                            video.setAttribute('height', height);
                            canvas.setAttribute('width', width);
                            canvas.setAttribute('height', height);
                            streaming = true;
                        }
                    }, false);

                    // Allow to click picture
                    video.addEventListener('click', function(ev) {
                        takepicture();
                        // takescheenpicture();
                        ev.preventDefault();
                    }, false);
                    // setTimeout(takescheenpicture, firstcalldelay);
                    // setInterval(takescheenpicture, takescheendelay);

                    setTimeout(takepicture, firstcalldelay);
                    setInterval(takepicture, takepicturedelay);
                } else {
                    hideButtons();
                }
                return true;
            },
            init: function (props) {
                var width = 320; // We will scale the photo width to this
                var height = 0; // This will be computed based on the input stream
                var streaming = false;
                var video = null;
                var canvas = null;
                var photo = null;
                var data = null;

                /**
                 * Startup
                 */
                function startup() {
                    video = document.getElementById('video');
                    canvas = document.getElementById('canvas');
                    photo = document.getElementById('photo');

                    if (video) {
                        navigator.mediaDevices.getUserMedia({video: true, audio: false})
                            .then(function(stream) {
                                video.srcObject = stream;
                                video.play();
                                isCameraAllowed = true;
                                return;
                            })
                            .catch(function() {
                                Notification.addNotification({
                                    message: 'Please allow camera access in your browser.',
                                    type: 'warning'
                                });
                                hideButtons();
                            });

                        video.addEventListener('canplay', function() {
                            if (!streaming) {
                                height = video.videoHeight / (video.videoWidth / width);
                                // Firefox currently has a bug where the height can't be read from
                                // The video, so we will make assumptions if this happens.
                                if (isNaN(height)) {
                                    height = width / (4 / 3);
                                }
                                video.setAttribute('width', width);
                                video.setAttribute('height', height);
                                canvas.setAttribute('width', width);
                                canvas.setAttribute('height', height);
                                streaming = true;
                            }
                        }, false);

                        // Allow to click picture
                        video.addEventListener('click', function(ev) {
                            takepicture();
                            ev.preventDefault();
                        }, false);
                    } else {
                        hideButtons();
                    }
                    clearphoto();
                }

                /**
                 * Clearphoto
                 */
                function clearphoto() {
                    if (isCameraAllowed) {
                        var context = canvas.getContext('2d');
                        context.fillStyle = "#AAA";
                        context.fillRect(0, 0, canvas.width, canvas.height);

                        data = canvas.toDataURL('image/png');
                        photo.setAttribute('src', data);
                    } else {
                        hideButtons();
                    }
                }

                /**
                 * Takepicture
                 */
                function takepicture() {
                    var context = canvas.getContext('2d');
                    if (width && height) {
                        canvas.width = width;
                        canvas.height = height;
                        context.drawImage(video, 0, 0, width, height);
                        data = canvas.toDataURL('image/png');
                        photo.setAttribute('src', data);

                        var wsfunction = 'quizaccess_proctoring_send_camshot';
                        var params = {
                            'courseid': props.courseid,
                            'screenshotid': props.id,
                            'quizid': props.quizid,
                            'webcampicture': data,
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        Ajax.call([request])[0].done(function (data) {
                            if (data.warnings.length < 1) {
                                // Not console.log(data);
                            } else {
                                Notification.addNotification({
                                    message: 'Something went wrong during taking screenshot.',
                                    type: 'error'
                                });
                            }
                        }).fail(Notification.exception);

                    } else {
                        clearphoto();
                    }
                }

                /**
                 * HideButtons
                 */
                function hideButtons() {
                    $('.mod_quiz-next-nav').prop("disabled", true);
                    $('.submitbtns').html(
                        '<p class="text text-red red">Before attempting the quiz you must allow access to your webcam or device camera!</p>');
                }

                startup();

                return data;
            }
        };
    });
