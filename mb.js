$(function () {
    var menuTopPadding = 50;

    //checking if device is smallScreen
    if (window.innerHeight < 800) {
        menuTopPadding = -50;
        $("header").css({ "height": "30px" });
    }

    $(".top .navigation").css({ "display": "block" });  //show navigation buttons

    //adding the custom scrollbar
    $(".contact").mCustomScrollbar({
        advanced: {
            updateOnContentResize: true
        }
    });

    var currentSection = ".top";
    var currentSection2 = undefined;
    var infoJSON;
    $(window).resize(resizeSection);
    $(window).load(resizeSection);


    //reading JSON and initialisations
    $.getJSON("portfolio.json", function (info) {
        infoJSON = info;

        //addig click event to the left image
        $("#left-img").click(function () {
            var previmage = $("#illustrationBar>div>a[class=active]>img").parent().prev().children();
            if (previmage.attr("big") === undefined) previmage = $("#illustrationBar>div>a:last>img");
            executeImageChange(previmage);
        });

        //addig click event to the right image
        $("#right-img").click(function () {
            var nextimage = $("#illustrationBar>div>a[class=active]>img").parent().next().children();
            if (nextimage.attr("big") === undefined) nextimage = $("#illustrationBar>div>a:first>img");
            executeImageChange(nextimage);
        });


        //main buttons click events
        $("#but1").click(function () {
            hideSection(currentSection, currentSection2)
                .then(function () { return initialiseSection(infoJSON.illustration); })
                    .then(function () { return showSection("#illustrationImg", ".img-bar") })
            //menu change:
            $(".menu1").animate({ "opacity": "0" }, function () {
                $(".menu1").css({ "display": "none" });
                $(".menu2").css({ "display": "block" });
                $(".menu2").animate({ "opacity": "1" });
            });

        });

        $("#but2").click(function () {
            hideSection(currentSection, currentSection2)
                .then(function () { return initialiseSection(infoJSON.personal); })
                    .then(function () { return showSection(".portfolio", ".img-bar") });
        });

        //$("#but3").click(function () {
        //    hideSection(currentSection, currentSection2).then(function () { return showSection(".prints") });
        //});

        $("#but4").click(function () {
            hideSection(currentSection, currentSection2).then(function () { return showSection(".contact") });
        });

        $("#but5").click(function () {
            //hideSection(currentSection, currentSection2).then(function () { return showSection(".portfolio", ".img-bar") });
            //hideSection(".menu2").then(function () { return showSection(".menu1") });
            $(".menu2").animate({ "opacity": "0" }, function () {
                $(".menu2").css({"display":"none"});
                $(".menu1").css({"display":"block"});
                $(".menu1").animate({ "opacity": "1" });
            });
        });

        $("#but6").click(function () {
            hideSection(currentSection, currentSection2)
                .then(function () { return initialiseSection(infoJSON.illustration); })
                    .then(function () { return showSection(".portfolio", ".img-bar") });
        });

        $("#but7").click(function () {
            hideSection(currentSection, currentSection2)
                .then(function () { return initialiseSection(infoJSON.characters); })
                    .then(function () { return showSection(".portfolio", ".img-bar") });
        });

        $("#but8").click(function () {
            hideSection(currentSection, currentSection2)
                .then(function () { return initialiseSection(infoJSON.environments); })
                    .then(function () {  return showSection(".portfolio", ".img-bar") });
        });

   });


    var initialiseSection = function (JSONsection) {
        var result = $.Deferred();

        //initialising illustrationBar start
        $("#illustrationBar>div")[0].innerHTML = "";
        $("#mid-img")[0].innerHTML = "";
        $("#right-img")[0].innerHTML = "";  //cleans left and right
        $("#left-img")[0].innerHTML = "";

        $("#loadingimagesection").css({ "display": "block" });

        for (img in JSONsection) {
            $("#illustrationBar>div").append('<a href="#"><img src="' +
                JSONsection[img].thumb + '" big="' + JSONsection[img].url + '" /></a>');
        }
        $("#illustrationBar>div>a:first").addClass("active");

        $("#mid-img").append('<img src="' + JSONsection.img1.url + '"/>');

        //initialising illustrationBar main images end
        $("#mid-img img").load(function () {
            $(".portfolio").css({ "display": "block" });
            result.resolve();
            loadSideImages();
        });

        //adding click events to the thumbs bar
        $("#illustrationBar>div>a>img").click(function () {
            executeImageChange(this);
        });

        return result.promise();
    }

    var loadSideImages = function () {

        $("#left-img")[0].innerHTML = "";
        $("#right-img")[0].innerHTML = "";
        var prevNewImage = $("#illustrationBar>div>a.active>img").parent().prev().children().attr("big");
        if (prevNewImage === undefined) prevNewImage = $("#illustrationBar>div>a:last>img").attr("big");

        var nextNewImage = $("#illustrationBar>div>a.active>img").parent().next().children().attr("big");
        if (nextNewImage === undefined) nextNewImage = $("#illustrationBar>div>a:first>img").attr("big");

        $("#left-img").append('<a href="#"><img src="' + prevNewImage + '"/></a>');
        $("#right-img").append('<a href="#"><img src="' + nextNewImage + '"/></a>');
        $("#left-img").css({ "opacity": "0" });
        $("#right-img").css({ "opacity": "0" });

        $("#left-img img").load(function () {
            var padLeft = ($(currentSection).height() - $("#left-img img").height()) / 2;
            $("#left-img").css({ "top": padLeft });
            $("#left-img>a").height($("#left-img>a>img").height());
            $("#left-img a").css({ "opacity": "0.5" });
            showElement("#left-img");
        });

        $("#right-img img").load(function () {
            var padRight = ($(currentSection).height() - $("#right-img img").height()) / 2;
            $("#right-img").css({ "top": padRight });
            $("#right-img>a").height($("#right-img>a>img").height());
            $("#right-img a").css({ "opacity": "0.5" });
            showElement("#right-img");
        });
    }

    //executes an image change to _this (_this is a <img> from thumb bar)
    var executeImageChange = function (_this) {

        hideElement("#left-img");
        hideElement("#right-img");
        $("#left-img img").unbind();
        $("#right-img img").unbind();

        $(".img-bar div").children().removeClass();
        $(_this).parent().addClass("active");

        var newimg = $(_this).attr("big");

        hideElement("#mid-img")
            .then(function () { return imgLoaded("#mid-img",newimg,false) })
                .then(function () {
                    loadSideImages();
                    return showElement("#mid-img");
                });
    };

    //commands to execute when window is loaded or resized
    function resizeSection() {

        var sectionHeight = $(window).height();
        if ($(currentSection).attr("class") === "portfolio") sectionHeight -= (275 + (menuTopPadding-50));   //if it's a portfolio then we substract the header and the bommot thumb bar
        if ($(currentSection).attr("class") === "contact mCustomScrollbar _mCS_1") sectionHeight -= (menuTopPadding + 130 + 90);   //if it's a contact then we only substract the header and #social-footer
        $("section").css({ "height": sectionHeight + "px" });

        var padMid = ($(currentSection).height() - $("#mid-img img").height()) / 2;
        $("#mid-img").css({ "top": padMid });
        if ($("#mid-img img").height() > $("#mid-img").height()) $("#mid-img img").height($("#mid-img").height());
    };


    //loads `image` in `parentElem` and ads an anchor tag is `anchor`===true
    var imgLoaded = function(parentElem, image, anchor){
        var result2 = $.Deferred();
        if (parentElem === "#mid-img") $("#loadingimage").css({ "display": "block" });

        $(parentElem)[0].innerHTML = "";
        if (!anchor) var newImgElem = $('<img src="' + image + '"/>');
        else var newImgElem = $('<a href="#"><img src="' + image + '"/></a>');    //// ads anchor - used for image on left and right
        $(parentElem).append(newImgElem);
        $("#mid-img img").load(function () {
            resizeSection();
            if (parentElem === "#mid-img") $("#loadingimage").css({ "display": "none" });
            result2.resolve();
        });

        return result2.promise();
    }

    //animates hiding an element
    var hideElement = function (elem) {
        var result1 = $.Deferred();

        if ($(elem).css("opacity") !== "0") {
            $(elem).animate({ "opacity": "0" }, function () {
                    $(elem).innerHTML = "";
                    result1.resolve();
            });
        } else result1.resolve();

        return result1.promise();
    };

    //animates showing an element
    var showElement = function (elem) {
        var result3 = $.Deferred();

        if ($(elem).css("opacity") === "0") {
            $(elem).animate({ "opacity": "1" }, function () {
                //$(elem).css({ "display": "none" });
                result3.resolve();
            });
        } else result3.resolve();

        return result3.promise();
    };

    //moves top menu from center
    var moveMenu = function () {
        var result = $.Deferred();

        //move the menu
        if ($(".top").css("top").split("px")[0] > menuTopPadding) $(".top").animate({ "top": menuTopPadding + "px" }, 1000, result.resolve);

        return result.promise();
    };

    //animates showing the specified sections   (section2 is only for the thumbs bar)
    var showSection = function(section,section2, curentBool){
        var result = $.Deferred();
            currentSection = section;
            currentSection2 = section2;

            if ((section2 !== undefined) && (section2 !== "")) {
                $(section2).css({ "display": "block" });

                //seting the size of thumb images
                if ($(".img-bar a").length * 210 > $(".img-bar").width()) {
                    var thumbWidth = (($(".img-bar").width()- 40) / $(".img-bar a").length) - 11;
                    $(".img-bar a").css({ "width": thumbWidth + "px" });
                }
                else $(".img-bar a").css({ "width": "200px" })

                $(section2).animate({ "opacity": "1" });
            }

        $(section).css({ "display": "block" });
        $("#loadingimagesection").css({ "display": "none" });
        resizeSection();
        $(section).animate({ "opacity": "1" }, result.resolve);
        return result.promise();
    };

    //animates hiding the specified sections
    var hideSection = function (section, section2) {
        var result = $.Deferred();

        $("#mid-img img").unbind();
        hideElement("#left-img");
        hideElement("#right-img");
        $("#left-img img").unbind();
        $("#right-img img").unbind();

        if (section === ".top") {
            moveMenu().then(function () { result.resolve(); })
        }
        else {
            if ((section2 !== undefined) || (section2 !== "")) {
                if ($(section2).css("display") !== "none") {
                    $(section2).animate({ "opacity": "0" }, function () {
                        $(section2).css({ "display": "none" });
                    });
                }
            }

            if ($(section).css("display") !== "none") {
                $(section).animate({ "opacity": "0" }, function () {
                    $(section).css({ "display": "none" });
                    result.resolve();
                });
            } else result.resolve();
        }

        currentSection = "";
        currentSection2 = "";
        return result.promise();
    };

});
