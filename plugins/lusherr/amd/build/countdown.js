/**
 * @module qbehaviour_lusherr/scripts
 */
define(['jquery'], function($) { // Moodle needs this to recognise $ https://docs.moodle.org/dev/jQuery .
    // JQuery is available via $.

    return {
        initialise: function(text) {
            // Module initialised.
            var buttonNext=document.getElementById('butTT');
            buttonNext.hidden=false;
            var nextButt = document.getElementsByName("next");
            nextButt.item(0).setAttribute("hidden","true");

            var navigation = document.getElementsByClassName("nav-link");

            expandation = navigation.item(0).getAttribute("aria-expanded");
            if(expandation === 'true') navigation.item(0).click();
            navigation.item(0).setAttribute("hidden", "true");

            var tested= document.getElementsByClassName("endtestlink");
            tested.item(0).setAttribute("hidden","true");

            localStorage.setItem('qtext', text);

            function getTimeRemaining(endtime) {
                var t = Date.parse(endtime) - Date.parse(new Date());
                var seconds = Math.floor((t / 1000) % 60);
                var minutes = Math.floor((t / 1000 / 60) % 60);
                return {
                    'total': t,
                    'minutes': minutes,
                    'seconds': seconds
                };
            }

            function initializeClock(id, endtime) {
                var buttonNext=document.getElementById('butTT');
                buttonNext.hidden=true;

                var clock = document.getElementById("time");
                clock.hidden=false;
                var minutesSpan = clock.querySelector('.minutes');
                var secondsSpan = clock.querySelector('.seconds');

                function updateClock() {
                    var t = getTimeRemaining(endtime);

                    minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
                    secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

                    if (t.total <= 0) {
                        clearInterval(timeinterval);
                        document.getElementsByName("next").item(0).hidden=false;
                    }
                }
                updateClock();
                var timeinterval = setInterval(updateClock, 1000);
            }


            var deadline = new Date(Date.parse(new Date()) + 3 * 60 * 1000); //интервал в 3 минуты между вопросами


            Array.prototype.map.call(document.querySelectorAll('button.butTT'),function(element){
                element.addEventListener('click',function(){
                    d1 = document.getElementById("countDrop");
                    if(d1.innerHTML == 8) {
                        initializeClock('countdown', deadline);
                        var qt = document.getElementsByClassName('qtext');
                        qt.item(0).innerHTML="<p>Методика тестирования предполагает два подхода с небольшим интервалом между ними.\n<\p>" +
                            "Не пытайтесь вспомнить тот порядок, в котором вы выбирали цвета в первый раз.\n" +
                            "Но и не старайтесь специально разложить их по-другому.<\p>\n" +
                            "Просто выбирайте понравившиеся цвета, как будто видите их впервые.";
                        var ddar = document.getElementsByClassName('ddarea');
                        ddar.item(0).hidden=true;
                    }
                },false)
            })

        }
    };
});