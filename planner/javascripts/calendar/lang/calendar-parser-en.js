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
var re_now = /^tod|now/i;
var re_tomorrow = /^tom/i;
var re_yesterday = /^yes/i;
var re_weekdays = /(^mon.*|^tue.*|^wed.*|^thu.*|^fri.*|^sat.*|^sun.*)/i;

var label_invalid_month = "Invalid month";
var label_ambiguous_month = "Ambiguous month";
var label_invalid_day = "Invalid day";
var label_ambiguous_weekday = "Ambiguous weekday";
var label_invalid_month_value = "Invalid month value.  Valid months values are 1 to 12";
var label_invalid_day_of_month_pre = "Invalid date value.  Valid date values for";
var label_invalid_day_of_month_post = "are 1 to";
var label_invalid_date = "Invalid date";

// This array is merged with defaultDateParsePatterns (in calendar-parser.js) into dateParsePatterns
// Here, you can define your own handler for the regular expression, as the order of the "bits" 
// may be different in a foreign language. 
// Example: "next week" yields the bits[1] == "next" and bits[2] == "week". 
// But in e.g. French, the order is "la semaine prochaine" (literally: 'the week next').  
var translatedDateParsePatterns = [
    // Tuesday last week          
    {   re: /(^mon.*|^tue.*|^wed.*|^thu.*|^fri.*|^sat.*|^sun.*)\s(last week)/i,
        handler: function(bits) {           
            var nwd = parseWeekday(bits[1]);

            objDate = new Date();            
            dd = objDate.getDate(); // get current day of the month                        
            newDay = dd - 7;            
            objDate.setDate(newDay); // 1 week ago            
            objDate.setDate(objDate.getDate() - objDate.getDay() ); // start of previous week (sunday or 0)                                    
            objDate.setDate(objDate.getDate() + nwd); // set to correct weekday

            return objDate;
            return objDate;
        }
    },    
    
    // Next Week, Last Week, Next Month, Last Month, Next Year, Last Year
    {   re: /((next|last)\s(week|month|year))/i,
        handler: function(bits) {          
            var objDate = new Date();
            
            var dd = objDate.getDate();
            var mm = objDate.getMonth();
            var yyyy = objDate.getFullYear();
            
            switch (bits[3]) {
              case "week":                                                                
                var newDay = (bits[2].indexOf("next") > -1) ? (dd + 7) : (dd - 7);                
                objDate.setDate(newDay);                
                break;
              case "month":
                var newMonth = (bits[2].indexOf("next") > -1) ? (mm + 1) : (mm - 1);                
                objDate.setMonth(newMonth);                
                break;
              case "year":                
                var newYear = (bits[2].indexOf("next") > -1) ? (yyyy + 1) : (yyyy - 1);                
                objDate.setYear(newYear);                
                break;
            }
            
            return objDate;
        }
    }, 
    
    // next Tuesday - this is suspect due to weird meaning of "next"
    {   re: /^next (\w+)$/i,
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
    // last Tuesday
    {   re: /^last (\w+)$/i,
        handler: function(bits) {

            var d = new Date();
            var wd = d.getDay();
            var nwd = parseWeekday(bits[1]);

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
    // 4th
    {   re: /^(\d{1,2})(st|nd|rd|th)?$/i, 
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear();
            var dd = parseInt(bits[1], 10);
            var mm = d.getMonth();

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // 4th Jan
    {   re: /^(\d{1,2})(?:st|nd|rd|th)? (?:of\s)?(\w+)$/i, 
        handler: function(bits) {
            var d = new Date();
            var yyyy = d.getFullYear();
            var dd = parseInt(bits[1], 10);
            var mm = parseMonth(bits[2]);

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // 4th Jan 2003
    {   re: /^(\d{1,2})(?:st|nd|rd|th)? (?:of )?(\w+),? (\d{4})$/i,
        handler: function(bits) {
            var d = new Date();
            d.setDate(parseInt(bits[1], 10));
            d.setMonth(parseMonth(bits[2]));
            d.setYear(bits[3]);
            return d;
        }
    },
    // Jan 4th
    {   re: /^(\w+) (\d{1,2})(?:st|nd|rd|th)?$/i, 
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear(); 
            var dd = parseInt(bits[2], 10);
            var mm = parseMonth(bits[1]);

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // Jan 4th 2003
    {   re: /^(\w+) (\d{1,2})(?:st|nd|rd|th)?,? (\d{4})$/i,
        handler: function(bits) {

            var yyyy = parseInt(bits[3], 10); 
            var dd = parseInt(bits[2], 10);
            var mm = parseMonth(bits[1]);

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },          
];

