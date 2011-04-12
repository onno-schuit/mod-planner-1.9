/////////////////////////////////////////////////////////////////////////////////////
// Modified and enhanced by:
//      Onno Schuit - http://solin.eu
//
//      Nathaniel Brown - http://nshb.net
//      Email: nshb@inimit.com
//
// Created by: 
//      Simon Willison - http://simon.incutio.com
//
// License:
//      GNU Lesser General Public License version 2.1 or above.
//      http://www.gnu.org/licenses/lgpl.html
//
/////////////////////////////////////////////////////////////////////////////////////


// Translations are used in array dateParsePatterns (calendar-parser.js)
// Guidelines for translations: 
// - use shortest form in translation (e.g.: in French 'prochain' instead of 'prochaine'),
// - but, preferably, use multiple forms in your regex /(prochain|prochaine)/i
// - start the array translatedDateParsePatterns with the most specific case: pattern for "January 5th 2005" comes before "January 5th"
// - include all patterns involving numbers AND words, or weekdays AND modifiers (such as "next") here (not in calendar-parser.js), because the patterns for these expressions will inevitably be language-specific
var re_now = /^van|nu/i;
var re_tomorrow = /^mor/i;
var re_yesterday = /^gis/i;
var re_weekdays = /(^maa.*|^din.*|^woe.*|^don.*|^vrij.*|^zat.*|^zon.*)/i;

var label_invalid_month = "Ongeldige maand";
var label_ambiguous_month = "Maand is onduidelijk";
var label_invalid_day = "Ongeldige dag";
var label_ambiguous_weekday = "Onduidelijke weekdag";
var label_invalid_month_value = "Ongeldige maand.  Geldige maanden zijn: 1 tot en met 12";
var label_invalid_day_of_month_pre = "Ongeldige datum.  Geldige data voor";
var label_invalid_day_of_month_post = "zijn 1 tot en met";
var label_invalid_date = "Ongeldige datum";

// This array is merged with defaultDateParsePatterns (in calendar-parser.js) into dateParsePatterns
// Here, you can define your own handler for the regular expression, as the order of the "bits" 
// may be different in a foreign language. 
// Example: "next week" yields the bits[1] == "next" and bits[2] == "week". 
// But in e.g. French, the order is "la semaine prochaine" (literally: 'the week next').  
var translatedDateParsePatterns = [
    // vorige week dinsdag, volgende week dinsdag        
    {   re: /(vorige week|volgende week)\s(maa*|din*|woe*|don*|vrij*|zat*|zon*)/i,
        handler: function(bits) {
            //alert('[Tuesday last week] bits[1] = ' + bits[1] + '; bits[2] = ' + bits[2]);
            
            var nwd = parseWeekday(bits[2]);

            objDate = new Date();            
            dd = objDate.getDate(); // get current day of the month                        
            newDay = (bits[1] == 'vorige week') ? dd - 7 : dd + 7;            
            objDate.setDate(newDay); // 1 week ago / later         
            objDate.setDate(objDate.getDate() - objDate.getDay() ); // start of previous / next week (sunday or 0)                                    
            objDate.setDate(objDate.getDate() + nwd); // set to correct weekday

            return objDate;
        }
    },
    // aanstaande dinsdag, komende dinsdag
    {   re: /^(aanstaande|komende) (\w+)$/i,
        handler: function(bits) {
          //alert('case: next Tuesday bits[1] = ' + bits[1] + ' bits[2] =' + bits[2]);
            var d = new Date();
            var day = d.getDay();
            var newDay = parseWeekday(bits[2]);
            var addDays = newDay - day;
            if (newDay <= day) {
                addDays += 7;
            }
            d.setDate(d.getDate() + addDays);
            return d;
        }
    },    
    // volgende week, vorige week, volgende maand, etc.    
    {   re: /((volgende|vorige|volgend|vorig)\s(week|maand|jaar))/i,
        handler: function(bits) {            
            var objDate = new Date();
            
            var dd = objDate.getDate();
            var mm = objDate.getMonth();
            var yyyy = objDate.getFullYear();
            
            switch (bits[3]) {
              case "week":                                                                
                var newDay = (bits[2].indexOf("volgend") > -1) ? (dd + 7) : (dd - 7);                
                objDate.setDate(newDay);                
                break;
              case "maand":
                var newMonth = (bits[2].indexOf("volgend") > -1) ? (mm + 1) : (mm - 1);                
                objDate.setMonth(newMonth);                
                break;
              case "jaar":                
                var newYear = (bits[2].indexOf("volgend") > -1) ? (yyyy + 1) : (yyyy - 1);                
                objDate.setYear(newYear);                
                break;
            }            
            return objDate;
        }
    },
  
    // dinsdag aanstaande (vs. "aanstaande dinsdag" which is already covered)
    {   re: /(^maa.*|^din.*|^woe.*|^don.*|^vrij.*|^zat.*|^zon.*)\s(aanstaande)/i,      
        handler: function(bits) {
            var d = new Date();
            var day = d.getDay();
            var newDay = parseWeekday(bits[1]);
            var addDays = newDay - day;
            if (newDay <= day) {
                addDays += 7;
            }
            d.setDate(d.getDate() + addDays);
            return d;
        }
    },
    // afgelopen dinsdag, vorige dinsdag
    {   re: /^(afgelopen|vorige) (\w+)$/i,
        handler: function(bits) {

            var d = new Date();
            var wd = d.getDay();
            var nwd = parseWeekday(bits[2]);

            // determine the number of days to subtract to get last weekday
            var addDays = (-1 * (wd + 7 - nwd)) % 7;

            // above calculate 0 if weekdays are the same so we have to change this to 7
            if (0 == addDays)
               addDays = -7;
            
            // adjust date and return
            d.setDate(d.getDate() + addDays);
            return d;

        }
    },

    // (de) 4de, 4e januari 2003
    {   re: /^(?:de\s|)(\d{1,2})(?:ste|de|e)? (\w+)? (\d{4})$/i,
        handler: function(bits) {          
            var yyyy = bits[3];
            var dd = parseInt(bits[1], 10);
            var mm = parseMonth(bits[2]);
            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);            
        }
    },     
    // (de) 4de, 4e januari
    {   re: /^(?:de\s|)(\d{1,2})(?:ste|de|e)? (\w+)$/i, 
        handler: function(bits) {
            var d = new Date();
            var yyyy = d.getFullYear();
            var dd = parseInt(bits[1], 10);
            var mm = parseMonth(bits[2]);

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // (de) 4e, 4de
    {   re: /^(?:de\s|)(\d{1,2})(e|de)?$/i, 
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear();
            var dd = parseInt(bits[1], 10);
            var mm = d.getMonth();

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },    
       
];
