// JS doc. Requires jQuery

/*
 * Deals with operations for the GuestBook HTML form.
 * A JS Prototype Class. Could have use the ES6 class syntax
 */ 
function FormGuestBook() {
    this.fieldPrefix = "visitor_";
    this.fields = ["name", "address", "email", "message"];  
}

/*
 * Operation(s) to set up the form.
 */
FormGuestBook.prototype.initialize = function() {
    $("#visitor_name").focus();
};

/*
 * Operations to wipe the form fields or reset them.
 */
FormGuestBook.prototype.clearFields = function() {
    for (let i=0; i<this.fields.length; i++) {
        let realFieldName = this.fields[i];
        let fieldId = "#" + this.fieldPrefix + realFieldName;
        $(fieldId).val("");
    }
};

/*
 * Retrieves the data values from the form fields and stores them in an object.
 */
FormGuestBook.prototype.fetchValues = function() {
    let formData = {};
    for (let i=0; i<this.fields.length; i++) {
        let realFieldName = this.fields[i];
        let fieldId = "#" + this.fieldPrefix + realFieldName;
        formData[realFieldName] = $(fieldId).val();
    }
    // Get the Google ReCaptcha field response
    let recaptchaResponse = grecaptcha.getResponse();
    formData["captcha"] = recaptchaResponse || "";

    return formData;
};

/*
 * Attempts to display a confirmation to the user after the form is submitted.
 */
FormGuestBook.prototype.displayResult = function(resultState) {
    let message = "", css = "";
    if (resultState == "good") {
        message = "Your entry has been successfully added";
        $("#formGuestBookResultsArea").css("color", "green");
    }
    else if (resultState == "bad") {
        message = "There was a problem.";
        $("#formGuestBookResultsArea").css("color", "red");
    }
    $("#formGuestBookResultsArea").html(message);
};

/*
 * Deals with form submission. Makes a POST request to the back end with 
 * entry data.
 */
FormGuestBook.prototype.handleSubmit = function() {
    let formData = this.fetchValues();
    let that = this;
    $.ajax({
        url: "/forms/guestbook/submit",
        method: "POST",
        data: JSON.stringify(formData),
        dataType: "json",
        success: function( result ) {
            that.displayResult("good");
            that.clearFields();
            that.initialize();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            that.displayResult("bad");
        }
    });
};

/*
 * Populates the "Guest Book Entries" table rows.
 */
let fillVisitorsTable = function(visitors) {
    let rows = []; 

    for (let i=0; i< visitors.length; i++) {
        let visitor = visitors[i];
        let row = "<tr>";
        row += "<td>" + visitor["Name"] + "</td>";
        row += "<td>" + visitor["Address"] + "</td>";
        row += "<td>" + visitor["Email"] + "</td>";
        row += "<td>" + visitor["Message"] + "</td>";
        row += "<td>" + visitor["IPAddress"] + "</td>";
        row += "<td>" + visitor["Platform"] + "</td>";
        row += "</tr>";
        rows.push(row);
    }
    $("#entriesRows").html(rows.join("\n"));
}
                    
/*
 * Makes a request to the back end to retrieve entries so far. The plan is to
 * make this paginating...
 */
let fetchVisitors = function(paginationRequestURL) {
    $.ajax({
        url: paginationRequestURL,
        method: "GET",
        data: "",
        success: function( result ) {
           console.log(result);
           fillVisitorsTable(result);//["rows"]);
        }
    });
};


$( document ).ready(function() {
    
    $("#btnSubmit").click(function() {
        formGuestBook.handleSubmit();
    });
    
    // Interaction with the php-paginator displays. Intercept & make an AJAX
    // request to get paginated results.
    $(".pagination li a").click(function(event) {
        event.preventDefault();
        let paginationRequestURL = $(this).attr("href");
        
        $(".pagination li").each(function() {
           $(this).removeClass("active"); 
        });
        $(this).parent("li").addClass("active");
        
        let textOfPageButton = $(this).html();
        // Disable these buttons for now as there's a problem with the PHP Paginator
        // and Twig directives.
        if (textOfPageButton.indexOf("Next") > -1 || 
            textOfPageButton.indexOf("Previous") > -1) {
            return false;
        }
        fetchVisitors(paginationRequestURL);
    });
    
    const formGuestBook = new FormGuestBook(); 
    formGuestBook.initialize();
    let defaultPaginationRequestURL = "/entries/get/1";
    fetchVisitors(defaultPaginationRequestURL);
    
    // Add this missing bit for the paginator
    let $btnPrevious = $("<li><a id=\"btnPrevious\" href=\"\">&laquo; Previous </a></li>");
    $btnPrevious.insertBefore("ul.pagination>li:first");
    // Disable it since there's a Twig problem
    $("#btnPrevious").click(function(event) {
       event.preventDefault();
    });
    
}); // End jQuery scope

