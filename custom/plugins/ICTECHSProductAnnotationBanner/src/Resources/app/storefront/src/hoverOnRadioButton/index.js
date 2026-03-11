import Plugin from 'src/plugin-system/plugin.class';

export default class HoverOnRadioButton extends Plugin {
    init() {
        document.addEventListener('click', function(event) {
            var clickedElement = event.target;

            var isRadioButtonClick = clickedElement.type === 'radio' || clickedElement.closest('input[type="radio"]');

            if (!isRadioButtonClick) {
                setTimeout(function() {
                    disabledRadioButtons()
                }, 500);
            }
        });

        const modalElement = document.getElementById('bundleMainProductModal');
        // Listen for the 'hidden.bs.modal' event
        modalElement.addEventListener('hidden.bs.modal', function () {
            disabledRadioButtons();
        });

        setTimeout(function() {
           disabledRadioButtons()
        }, 500);

        this.radioButtonClickedValue = document.querySelectorAll('input[name=ProductRadioButton]');
        this.cancelButtonClickedValue = document.getElementById('remove-selected-product-view-button');
        function disabledRadioButtons(){
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(button => {
                button.checked = false;
            });
        }
        this._registerEvents();
    }

    _registerEvents() {

        for (let i = 0; i < this.radioButtonClickedValue.length; i++) {
            document.getElementById(this.radioButtonClickedValue[i].id).addEventListener('click', event => {
                let radioButtonClickedObject = JSON.parse(this.radioButtonClickedValue[i].value);
                if (radioButtonClickedObject.LayoutType === "fullWidth") {
                    if(radioButtonClickedObject.productManufacturerId !== null){
                        var manufactureUrl = ''
                        var manufactureName = ''
                        var manufactureDescription = ''

                        if(radioButtonClickedObject.manufacturer.media) {
                            // var manufactureUrl = radioButtonClickedObject.manufacturer.media.url;
                            var manufactureUrl =  " <img src='" + radioButtonClickedObject.manufacturer.media.url + "' class='img-fluid quickview-minimal-img'>";
                        }else{
                            var manufactureUrl = "";
                        }
                        if(radioButtonClickedObject.manufacturer.translated) {
                            var manufactureName = radioButtonClickedObject.manufacturer.translated.name;
                        }
                        if(radioButtonClickedObject.manufacturer.translated.description != null ) {
                            var manufactureDescription = radioButtonClickedObject.manufacturer.translated.description;
                        }

                        document.getElementById("manufacturer-details").innerHTML = "<div class='modal-body'><div class='quickview-minimal-container'><div class='row quickview-minimal-top'> <div class='col-12 col-md-6 quickview-minimal-image'>" +
                            manufactureUrl +
                            "</div>" +
                            "<div class='col-12 col-md-6 quickview-minimal-product'>" +
                            "<a href='#'class='h4 quickview-minimal-product-name'> " + manufactureName + " </a>" +
                            " <a href='' class='quickview-minimal-product-manufacturer'></a>" +
                            "<div class='quickview-minimal-footer'>" +
                            // "<p class='h5 quickview-minimal-footer-heading'>"+ radioButtonClickedObject.productDescriptionButtonTitle + "</p>" +
                            "<p class='quickview-minimal-footer-description'>" + manufactureDescription + "</p>" +
                            "</div>" +
                            "</div>" +
                            "</div>";
                    }else if (radioButtonClickedObject.categoryId !== null) {
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.matchMedia("(max-width: 1199px)").matches) {
                            let tooltiptext = findNearestTooltiptext(this);
                            if (tooltiptext) {
                                toggleVisibilityAndDisable(tooltiptext);
                            }
                        }
                    } else {
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.matchMedia("(max-width: 1199px)").matches) {
                            // Find the nearest tooltiptext div
                            var tooltiptext = findNearestTooltiptext(this);

                            // Toggle visibility and disable inputs
                            if (tooltiptext) {
                                toggleVisibilityAndDisable(tooltiptext);
                            }
                        }
                    }

                } else {
                    var crossSign = document.getElementById("product-annotation-cross-sign").innerHTML;
                    let htmlForImage = '';
                    if (radioButtonClickedObject.productImageUrls !== null && radioButtonClickedObject.categoryId == null && radioButtonClickedObject.productManufacturerId == null) {
                        htmlForImage += "<div class='productImageOnBanner'><img src='" + radioButtonClickedObject.productCoverImage + "'></div>"

                    } else if(radioButtonClickedObject.categoryId !== null ){

                        if(radioButtonClickedObject.category.media) {
                            var categoryImageUrl =  " <img src='" + radioButtonClickedObject.category.media.url + "' class='img-fluid quickview-minimal-img'>";
                        }else{
                            var categoryImageUrl = "";
                        }

                        htmlForImage = "<div class='productImageOnBanner'>"+categoryImageUrl+"</div>"

                    }else if(radioButtonClickedObject.productManufacturerId !== null){

                        if(radioButtonClickedObject.manufacturer.media) {
                            var manufactureImageUrl =  " <img src='" + radioButtonClickedObject.manufacturer.media.url + "' class='img-fluid quickview-minimal-img'>";
                        }else{
                            var manufactureImageUrl = "";
                        }
                        htmlForImage = "<div class='productImageOnBanner'>"+manufactureImageUrl+"</div>"
                    }
                    else {
                        htmlForImage = "";
                    }

                    var name = '';
                    var productNumber = '';
                    var productCurrency = '';
                    var productPrice = '';
                    var url = '';
                    var description = '';
                    var staticClass = '';

                    if(radioButtonClickedObject.categoryId !== null){
                        // category
                        var name = radioButtonClickedObject.categoryName;
                        var productNumber = '';
                        var productCurrency = '';
                        var productPrice = '';
                        var url = radioButtonClickedObject.categoryDetailPageUrl;

                        if(radioButtonClickedObject.category.translated.description != null) {
                            var description = radioButtonClickedObject.category.translated.description;
                        }
                    } else if(radioButtonClickedObject.productManufacturerId !== null) {
                        // manufacturer
                        var name = radioButtonClickedObject.manufacturer.translated.name;
                        var url = '';
                        var staticClass = 'd-none';
                        if(radioButtonClickedObject.manufacturer.translated.description) {
                            var description = radioButtonClickedObject.manufacturer.translated.description;
                        }else{
                            var description = '';
                        }
                    }else {
                        // product
                        var name = radioButtonClickedObject.productName;
                        var productNumber = radioButtonClickedObject.productNumber;
                        var productCurrency = radioButtonClickedObject.salesChannelCurrency;
                        var productPrice = radioButtonClickedObject.productPrice;
                        var url = radioButtonClickedObject.productDetailPageUrl;
                        if(radioButtonClickedObject.productDescription) {
                            var description = radioButtonClickedObject.productDescription;
                        }else{
                            var description = '';
                        }
                    }

                    document.getElementById("product-banner-full-width-text-section-click-to-change").innerHTML = "<div class='js_call_product_name'>" +
                        crossSign +
                        "                <h1>" + name + "</h1>\n" +
                        "            </div>\n" +
                        "            <div class='js_call_product_price'>\n" +
                        "                <h3>" + productCurrency + " " + productPrice + "</h3>\n" +
                        "            </div>\n" +
                        "            <div class='js_call_product_image'>\n" +
                        "                " + htmlForImage + " \n" +
                        "            </div>" +
                        "            <div class='js_call_description'>\n" +
                        "                <p>" + description + " </p>\n" +
                        "            </div>\n" +

                        "            <div class='js_call_product_detail_page_button'>\n" +
                        "                <a class='" + staticClass + "' href='" + url + "'><button type='button' class='btn btn-primary'>" + radioButtonClickedObject.productDetailButtonTitle + "</button></a> \n" +
                        "            </div>";
                }
            });
        }

        function findNearestTooltiptext(element) {

            var currentElement = element;
            while (currentElement) {
                // Check if currentElement is a tooltip element
                if (currentElement.classList && currentElement.classList.contains('tooltiptext')) {
                    return currentElement;
                }
                // Move up to parent element
                currentElement = currentElement.parentElement;
            }

            return null;
        }

        function toggleDisable(tooltiptext) {
            var inputs = tooltiptext.querySelectorAll('input, select, textarea, button');
            inputs.forEach(function(input) {
                input.disabled = !input.disabled;
            });
        }

        // Function to toggle visibility and disable inputs
        function toggleVisibilityAndDisable(tooltiptext) {
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(button => {
                button.checked = false;
            });


            // Toggle visibility
            tooltiptext.style.display = (tooltiptext.style.display === 'none' || tooltiptext.style.display === '') ? 'block' : 'none';

            // Disable inputs inside tooltiptext
            var inputs = tooltiptext.querySelectorAll('input, select, textarea, button',);
            inputs.forEach(function(input) {

                input.disabled = !input.disabled;
            });
        }
    }
}

