window.rebuildAddAllForm = function rebuildAddAllForm(btn) {
            const container = btn.closest('.cms-element-ict-shop-the-look');
            if (!container) return;
            const addAllForm = container.querySelector('.add-all-form');
            if (!addAllForm) return;
            
            addAllForm.querySelectorAll('.product-line-item, .variant-option').forEach(input => {
                input.remove();
            });
            
            const checkedProducts = container.querySelectorAll('.product-select-checkbox:checked');
            
            checkedProducts.forEach((checkbox, index) => {
                const productId = checkbox.dataset.productId;
                const productItem = checkbox.closest('.product-item');
                
                
                // Get selected options
                const selectedOptions = [];
                productItem.querySelectorAll('.variant-radio:checked').forEach(radio => {
                    if (radio.dataset.productId === productId) {
                        selectedOptions.push(radio.value);
                    }
                });
                
                // Check for variant data
                const variantDataScript = container.querySelector(`.variant-data[data-product-id="${productId}"]`);
                let variantIdToUse = productId;
                
                if (variantDataScript && selectedOptions.length > 0) {
                    const variantData = JSON.parse(variantDataScript.textContent);
                    
                    // Find matching variant
                    const matchingVariant = variantData.variants.find(variant => {
                        const hasAll = selectedOptions.every(opt => variant.options.includes(opt));
                        const sameLength = variant.options.length === selectedOptions.length;
                        return hasAll && sameLength;
                    });
                    
                    if (matchingVariant) {
                        variantIdToUse = matchingVariant.id;
                    } else {
                    }
                } else {
                }
                
                // Add form inputs — increment quantity if same variant already added
                const existingQty = addAllForm.querySelector(`input[name="lineItems[${variantIdToUse}][quantity]"]`);
                if (existingQty) {
                    existingQty.value = String(parseInt(existingQty.value, 10) + 1);
                    return;
                }

                const inputs = [
                    { name: `lineItems[${variantIdToUse}][id]`, value: variantIdToUse },
                    { name: `lineItems[${variantIdToUse}][type]`, value: 'product' },
                    { name: `lineItems[${variantIdToUse}][referencedId]`, value: variantIdToUse },
                    { name: `lineItems[${variantIdToUse}][quantity]`, value: '1' },
                    { name: `lineItems[${variantIdToUse}][stackable]`, value: '1' },
                    { name: `lineItems[${variantIdToUse}][removable]`, value: '1' }
                ];
                
                inputs.forEach(inputData => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = inputData.name;
                    input.value = inputData.value;
                    input.classList.add('product-line-item');
                    input.setAttribute('data-product-id', productId);
                    addAllForm.appendChild(input);
                });
                
                // Add options
                selectedOptions.forEach(optionId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `lineItems[${variantIdToUse}][payload][options][${optionId}]`;
                    input.value = optionId;
                    input.classList.add('variant-option', 'product-line-item');
                    input.setAttribute('data-product-id', productId);
                    addAllForm.appendChild(input);
                });
            });
            
        }
        
        window.debugFormData = debugFormData;

        // Debug function to check current selections
        function debugCurrentSelections() {
            document.querySelectorAll('.product-item').forEach((productItem, index) => {
                const productId = productItem.dataset.productId;
                
                productItem.querySelectorAll('.variant-radio').forEach(radio => {
                });
            });
        }
        
        // Debug function to show form data before submission
        function debugFormData(button) {
            const form = button.closest('form');
            const formData = new FormData(form);
            
            // Get product ID from button or form context
            let productId = button.dataset.productId;
            if (!productId && form.classList.contains('add-all-form')) {
                productId = 'multiple products';
            }
            
            
            for (let [key, value] of formData.entries()) {
            }
            
            form.querySelectorAll('input').forEach(input => {
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {

            // Scope all queries to the nearest shop-the-look container
            const containers = document.querySelectorAll('.cms-element-ict-shop-the-look');
            containers.forEach(function(container) { initContainer(container); });

            function initContainer(container) {
            
            // Product checkbox functionality
            container.querySelectorAll('.product-select-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const productItem = this.closest('.product-item');
                    const form = productItem.querySelector('.add-to-cart-form');
                    
                    if (this.checked) {
                        productItem.classList.remove('disabled');
                        if (form) form.style.display = 'block';
                    } else {
                        productItem.classList.add('disabled');
                        if (form) form.style.display = 'none';
                    }
                    
                    // Update the add-all form with current selections
                    updateAddAllButton();
                });
            });
            
            // Hotspot click to show popup and highlight product
            container.querySelectorAll('.shop-the-look-hotspot').forEach(hotspot => {
                hotspot.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const productId = this.dataset.productId;
                    const productItem = container.querySelector(`.product-item[data-product-id="${productId}"]`);
                    
                    // Close all other popups
                    container.querySelectorAll('.shop-the-look-hotspot').forEach(h => {
                        h.classList.remove('active');
                    });
                    
                    // Toggle this popup
                    this.classList.toggle('active');
                    
                    // Remove previous highlights
                    container.querySelectorAll('.product-item').forEach(item => {
                        item.classList.remove('highlighted');
                    });
                    
                    // Highlight clicked product
                    if (productItem && this.classList.contains('active')) {
                        productItem.classList.add('highlighted');
                        productItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
            
            // Close popups when clicking outside
            document.addEventListener('click', function() {
                container.querySelectorAll('.shop-the-look-hotspot').forEach(hotspot => {
                    hotspot.classList.remove('active');
                });
                container.querySelectorAll('.product-item').forEach(item => {
                    item.classList.remove('highlighted');
                });
            });
            
            // Handle variant selection for individual forms
            container.querySelectorAll('.variant-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const productId = this.dataset.productId;
                    const individualForm = this.closest('.product-item').querySelector('.add-to-cart-form');
                    const addAllForm = container.querySelector('.add-all-form');
                    
                    if (!productId) {
                        return;
                    }
                    
                    
                    // Get variant data for this product
                    const variantDataScript = container.querySelector(`.variant-data[data-product-id="${productId}"]`);
                    
                    if (variantDataScript) {
                        // This product has variants - find the correct one
                        const variantData = JSON.parse(variantDataScript.textContent);
                        const productItem = this.closest('.product-item');
                        const selectedOptions = [];
                        
                        // Collect all selected options for this product
                        productItem.querySelectorAll('.variant-radio:checked').forEach(selectedRadio => {
                            if (selectedRadio.dataset.productId === productId) {
                                selectedOptions.push(selectedRadio.value);
                            }
                        });
                        
                        
                        // Find the variant that matches all selected options
                        let matchingVariant = findMatchingVariant(variantData.variants, selectedOptions);
                        
                        if (matchingVariant) {
                            // Update individual form
                            if (individualForm) {
                                updateFormForVariant(individualForm, matchingVariant.id, selectedOptions);
                            }
                            // Update add all form
                            if (addAllForm) {
                                updateAddAllFormForProduct(addAllForm, productId, matchingVariant.id, selectedOptions);
                                // Also refresh the entire add-all form to ensure consistency
                                updateAddAllButton();
                            }
                        } else {
                            // No exact match found, using best match or first variant
                            // Fallback: find variant with most matching options
                            let bestMatch = findBestMatchingVariant(variantData.variants, selectedOptions);
                            if (bestMatch) {
                                if (individualForm) {
                                    updateFormForVariant(individualForm, bestMatch.id, selectedOptions);
                                }
                                if (addAllForm) {
                                    updateAddAllFormForProduct(addAllForm, productId, bestMatch.id, selectedOptions);
                                    updateAddAllButton();
                                }
                            } else if (variantData.variants.length > 0) {
                                // Last resort: use first variant
                                if (individualForm) {
                                    updateFormForVariant(individualForm, variantData.variants[0].id, selectedOptions);
                                }
                                if (addAllForm) {
                                    updateAddAllFormForProduct(addAllForm, productId, variantData.variants[0].id, selectedOptions);
                                    updateAddAllButton();
                                }
                            }
                        }
                    } else {
                        // This product doesn't have variants - just update options
                        const selectedOptions = [];
                        const productItem = this.closest('.product-item');
                        productItem.querySelectorAll('.variant-radio:checked').forEach(selectedRadio => {
                            if (selectedRadio.dataset.productId === productId) {
                                selectedOptions.push(selectedRadio.value);
                            }
                        });
                        
                        // Update individual form
                        if (individualForm) {
                            updateFormForVariant(individualForm, productId, selectedOptions);
                        }
                        // Update add all form
                        if (addAllForm) {
                            updateAddAllFormForProduct(addAllForm, productId, productId, selectedOptions);
                            updateAddAllButton();
                        }
                    }
                });
            });
            
            function findMatchingVariant(variants, selectedOptions) {
                const match = variants.find(variant => {
                    // Check if this variant has all the selected options
                    const hasAllOptions = selectedOptions.every(optionId => {
                        const hasOption = variant.options.includes(optionId);
                        return hasOption;
                    });
                    // And check if the variant doesn't have extra options
                    const noExtraOptions = variant.options.length === selectedOptions.length;
                    
                    const isMatch = hasAllOptions && noExtraOptions;
                    return isMatch;
                });
                return match;
            }
            
            function findBestMatchingVariant(variants, selectedOptions) {
                let bestMatch = null;
                let maxMatches = 0;
                
                variants.forEach(variant => {
                    const matches = selectedOptions.filter(optionId => {
                        const hasOption = variant.options.includes(optionId);
                        return hasOption;
                    }).length;
                    
                    if (matches > maxMatches) {
                        maxMatches = matches;
                        bestMatch = variant;
                    }
                });
                
                return bestMatch;
            }
            
            function updateFormForVariant(form, variantId, selectedOptions) {
                
                // Remove all existing lineItems inputs
                form.querySelectorAll('input[name*="lineItems["]').forEach(input => {
                    input.remove();
                });
                
                // Create complete new set of inputs for the variant
                const inputs = [
                    { name: `lineItems[${variantId}][id]`, value: variantId },
                    { name: `lineItems[${variantId}][type]`, value: 'product' },
                    { name: `lineItems[${variantId}][referencedId]`, value: variantId },
                    { name: `lineItems[${variantId}][quantity]`, value: '1' },
                    { name: `lineItems[${variantId}][stackable]`, value: '1' },
                    { name: `lineItems[${variantId}][removable]`, value: '1' }
                ];
                
                inputs.forEach(inputData => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = inputData.name;
                    input.value = inputData.value;
                    form.appendChild(input);
                });
                
                // Add option inputs for selected variants
                selectedOptions.forEach(optionId => {
                    const optionInput = document.createElement('input');
                    optionInput.type = 'hidden';
                    optionInput.name = `lineItems[${variantId}][payload][options][${optionId}]`;
                    optionInput.value = optionId;
                    optionInput.classList.add('variant-option');
                    form.appendChild(optionInput);
                });
                
            }
            
            function updateAddAllFormForProduct(addAllForm, originalProductId, variantId, selectedOptions) {
                
                // Remove all existing inputs for this product (both original and variant)
                addAllForm.querySelectorAll(`input[data-product-id="${originalProductId}"]`).forEach(input => {
                    input.remove();
                });
                addAllForm.querySelectorAll(`input[name*="[${originalProductId}]"]`).forEach(input => {
                    input.remove();
                });
                addAllForm.querySelectorAll(`input[name*="[${variantId}]"]`).forEach(input => {
                    input.remove();
                });
                
                // Create complete new set of inputs for the variant
                const inputs = [
                    { name: `lineItems[${variantId}][id]`, value: variantId },
                    { name: `lineItems[${variantId}][type]`, value: 'product' },
                    { name: `lineItems[${variantId}][referencedId]`, value: variantId },
                    { name: `lineItems[${variantId}][quantity]`, value: '1' },
                    { name: `lineItems[${variantId}][stackable]`, value: '1' },
                    { name: `lineItems[${variantId}][removable]`, value: '1' }
                ];
                
                inputs.forEach(inputData => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = inputData.name;
                    input.value = inputData.value;
                    input.classList.add('product-line-item');
                    input.setAttribute('data-product-id', originalProductId);
                    addAllForm.appendChild(input);
                });
                
                // Add option inputs for selected variants
                selectedOptions.forEach(optionId => {
                    const optionInput = document.createElement('input');
                    optionInput.type = 'hidden';
                    optionInput.name = `lineItems[${variantId}][payload][options][${optionId}]`;
                    optionInput.value = optionId;
                    optionInput.classList.add('variant-option', 'product-line-item');
                    optionInput.setAttribute('data-product-id', originalProductId);
                    addAllForm.appendChild(optionInput);
                });
                
            }
            
            function updateAddAllButton() {
                const checkedProducts = container.querySelectorAll('.product-select-checkbox:checked');
                const addAllButton = container.querySelector('.add-all-to-cart');
                const addAllForm = container.querySelector('.add-all-form');
                
                
                if (addAllButton && addAllForm) {
                    if (checkedProducts.length === 0) {
                        addAllButton.disabled = true;
                        addAllButton.textContent = 'SELECT PRODUCTS';
                    } else {
                        addAllButton.disabled = false;
                        addAllButton.textContent = `ADD ${checkedProducts.length} TO CART`;
                        
                        // Remove all existing product inputs from add-all form
                        addAllForm.querySelectorAll('.product-line-item, .variant-option').forEach(input => {
                            input.remove();
                        });
                        
                        // Add inputs only for checked products with their current variants
                        checkedProducts.forEach((checkbox, index) => {
                            const productId = checkbox.dataset.productId;
                            const productItem = checkbox.closest('.product-item');
                        
                            
                            // Get currently selected options for this product
                            const selectedOptions = [];
                            productItem.querySelectorAll('.variant-radio:checked').forEach(radio => {
                                if (radio.dataset.productId === productId) {
                                    selectedOptions.push(radio.value);
                                }
                            });
                            
                            
                            // Check if this product has variant data
                            const variantDataScript = container.querySelector(`.variant-data[data-product-id="${productId}"]`);
                            
                            if (variantDataScript && selectedOptions.length > 0) {
                                // This product has variants - find the correct one
                                const variantData = JSON.parse(variantDataScript.textContent);
                                
                                let matchingVariant = findMatchingVariant(variantData.variants, selectedOptions);
                                
                                if (!matchingVariant) {
                                    matchingVariant = findBestMatchingVariant(variantData.variants, selectedOptions);
                                }
                                
                                if (matchingVariant) {
                                    addProductToAddAllForm(addAllForm, productId, matchingVariant.id, selectedOptions);
                                } else {
                                    addProductToAddAllForm(addAllForm, productId, productId, selectedOptions);
                                }
                            } else {
                                // Product without variants or with simple options
                                addProductToAddAllForm(addAllForm, productId, productId, selectedOptions);
                            }
                        });
                        
                        addAllForm.querySelectorAll('input[name*="lineItems"]').forEach(input => {

                        });
                    }
                }
            }
            
            function addProductToAddAllForm(addAllForm, originalProductId, variantId, selectedOptions) {
                // If this variantId already exists in the form, just increment its quantity
                const existingQty = addAllForm.querySelector(`input[name="lineItems[${variantId}][quantity]"]`);
                if (existingQty) {
                    existingQty.value = String(parseInt(existingQty.value, 10) + 1);
                    return;
                }

                const inputs = [
                    { name: `lineItems[${variantId}][id]`, value: variantId },
                    { name: `lineItems[${variantId}][type]`, value: 'product' },
                    { name: `lineItems[${variantId}][referencedId]`, value: variantId },
                    { name: `lineItems[${variantId}][quantity]`, value: '1' },
                    { name: `lineItems[${variantId}][stackable]`, value: '1' },
                    { name: `lineItems[${variantId}][removable]`, value: '1' }
                ];

                inputs.forEach(inputData => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = inputData.name;
                    input.value = inputData.value;
                    input.classList.add('product-line-item');
                    input.setAttribute('data-product-id', originalProductId);
                    addAllForm.appendChild(input);
                });

                selectedOptions.forEach(optionId => {
                    const optionInput = document.createElement('input');
                    optionInput.type = 'hidden';
                    optionInput.name = `lineItems[${variantId}][payload][options][${optionId}]`;
                    optionInput.value = optionId;
                    optionInput.classList.add('variant-option', 'product-line-item');
                    optionInput.setAttribute('data-product-id', originalProductId);
                    addAllForm.appendChild(optionInput);
                });
            }
            
            // Initialize forms with correct variant data on page load
            container.querySelectorAll('.product-item').forEach(productItem => {
                const productId = productItem.dataset.productId;
                
                if (productId) {
                    // Initialize with currently selected variants
                    initializeProductVariants(productItem, productId);
                }
            });
            
            function initializeProductVariants(productItem, productId) {
                const individualForm = productItem.querySelector('.add-to-cart-form');
                const addAllForm = container.querySelector('.add-all-form');
                
                // Get currently selected options for this product
                const selectedOptions = [];
                productItem.querySelectorAll('.variant-radio:checked').forEach(radio => {
                    if (radio.dataset.productId === productId) {
                        selectedOptions.push(radio.value);
                    }
                });
                
                
                // Check if this product has variant data
                const variantDataScript = container.querySelector(`.variant-data[data-product-id="${productId}"]`);
                
                if (variantDataScript && selectedOptions.length > 0) {
                    // This product has variants - find the correct one
                    const variantData = JSON.parse(variantDataScript.textContent);
                    let matchingVariant = findMatchingVariant(variantData.variants, selectedOptions);
                    
                    if (!matchingVariant) {
                        matchingVariant = findBestMatchingVariant(variantData.variants, selectedOptions);
                    }
                    
                    if (matchingVariant) {
                        // Update individual form
                        if (individualForm) {
                            updateFormForVariant(individualForm, matchingVariant.id, selectedOptions);
                        }
                        // Update add all form
                        if (addAllForm) {
                            updateAddAllFormForProduct(addAllForm, productId, matchingVariant.id, selectedOptions);
                        }
                    }
                } else if (selectedOptions.length > 0) {
                    // Product without variants but has options (properties)
                    if (individualForm) {
                        updateFormForVariant(individualForm, productId, selectedOptions);
                    }
                    if (addAllForm) {
                        updateAddAllFormForProduct(addAllForm, productId, productId, selectedOptions);
                    }
                } else {
                    // Product without any variants or options
                    if (addAllForm) {
                        updateAddAllFormForProduct(addAllForm, productId, productId, []);
                    }
                }
            }
            
            // Initialize
            updateAddAllButton();

            } // end initContainer
        });