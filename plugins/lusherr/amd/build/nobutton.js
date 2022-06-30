let define1 = define(['jquery'], function($) { // Moodle needs this to recognise $ https://docs.moodle.org/dev/jQuery .
    // JQuery is available via $.

    return {
        init: function () {


            //var prevButt = document.getElementsByName("previous");
            //if(!prevButt) prevButt.item(0).setAttribute("hidden", "true");

            var nextButt = document.getElementsByName("next");


            nextButt.item(0).onclick = function() {
                d1 = document.getElementById("countDrop");
                if(!d1) return true;
                if(d1.innerHTML == 8) {
                    return true;
                }

                //предотвращаем переход по ссылке href
                return false;
            }



        }
    }
});