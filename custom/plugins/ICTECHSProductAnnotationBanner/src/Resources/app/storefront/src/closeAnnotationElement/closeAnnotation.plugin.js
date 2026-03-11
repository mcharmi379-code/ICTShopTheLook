import Plugin from "src/plugin-system/plugin.class";

export default class CloseAnnotationElement extends Plugin {
    init() {
        this._registerEvents();
    }

    _registerEvents() {
        const radioButtonClickedValue = document.querySelectorAll('input[name="ProductRadioButton"]');

        radioButtonClickedValue.forEach((radio) => {
            radio.addEventListener("change", (event) => this.closeAnnotationElement(event));
        });
    }

    closeAnnotationElement(event) {  
        const selectedRadio = event.target; 
        const bannerTextElement = document.getElementById("product-banner-full-width-text-section-click-to-change");

        if (bannerTextElement) {
            const removeButton = bannerTextElement.querySelector("#remove-selected-product-view-button");
          
            removeButton.addEventListener("click", function () {               
                    bannerTextElement.innerHTML = removeButton.getAttribute("annotation-banner-text-content");

                    if (selectedRadio) {
                        selectedRadio.checked = false;
                    }
            });
        }
    }  
}
