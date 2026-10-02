
let currentStep = 1;
const totalSteps = 9;
const surveyData = {};

// Grocery items for each diet type
/*const groceryItems = {
    omnivore: [
        'Rice', 'Pasta', 'Bread', 'Chicken', 'Beef', 'Pork', 'Eggs', 'Milk', 
        'Cheese', 'Yogurt', 'Potatoes', 'Tomatoes', 'Onions', 'Carrots', 
        'Lettuce', 'Apples', 'Bananas', 'Oranges', 'Flour', 'Sugar'
    ],
    vegetarian: [
        'Rice', 'Pasta', 'Bread', 'Eggs', 'Milk', 'Cheese', 'Yogurt', 
        'Butter', 'Potatoes', 'Tomatoes', 'Onions', 'Carrots', 'Lettuce', 
        'Beans', 'Lentils', 'Apples', 'Bananas', 'Oranges', 'Flour', 'Sugar'
    ],
    pescatarian: [
        'Rice', 'Pasta', 'Bread', 'Fish', 'Shrimp', 'Salmon', 'Eggs', 
        'Milk', 'Cheese', 'Yogurt', 'Potatoes', 'Tomatoes', 'Onions', 
        'Carrots', 'Lettuce', 'Apples', 'Bananas', 'Oranges', 'Flour', 'Sugar'
    ],
    vegan: [
        'Rice', 'Pasta', 'Bread', 'Tofu', 'Beans', 'Lentils', 'Chickpeas', 
        'Almond Milk', 'Potatoes', 'Tomatoes', 'Onions', 'Carrots', 'Lettuce', 
        'Spinach', 'Apples', 'Bananas', 'Oranges', 'Flour', 'Sugar', 'Nuts'
    ]
};*/


// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    setupEventListeners();
});

function setupEventListeners() {
    // Agreement checkboxes
    const agreement1 = document.getElementById('agreement1');
    if (agreement1) {
        agreement1.addEventListener('change', function() {
            document.getElementById('step1Next').disabled = !this.checked;
        });
    }

    const agreement2 = document.getElementById('agreement2');
    if (agreement2) {
        agreement2.addEventListener('change', function() {
            document.getElementById('step3Next').disabled = !this.checked;
        });
    }

    // Radio button groups
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Update selected card styling
            this.closest('.option-group').querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
            });
            this.closest('.option-card').classList.add('selected');

            // Enable next button for the current step
            const stepNum = parseInt(this.closest('.step').dataset.step);
            const nextBtn = document.getElementById(`step${stepNum}Next`);
            if (nextBtn) nextBtn.disabled = false;
        });
    });
}

function nextStep() {
    const currentStepEl = document.querySelector(`.step[data-step="${currentStep}"]`);
    
    // Validate current step
    if (!validateStep(currentStep)) {
        return;
    }

    // Save data from current step
    saveStepData(currentStep);

    // Special handling for step 3 (going to groceries)
    if (currentStep === 3) {
        populateGroceries();
    }

    // Move to next step
    currentStepEl.classList.remove('active');
    currentStep++;
    document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
    updateProgress();
    window.scrollTo(0, 0);
}

function prevStep() {
    document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
    currentStep--;
    document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
    updateProgress();
    window.scrollTo(0, 0);
}

function validateStep(step) {
    const errorMsg = document.getElementById('errorMessage');
    errorMsg.style.display = 'none';

    switch(step) {
        case 1:
            if (!document.getElementById('agreement1').checked) {
                showError('Please agree to the terms to continue.');
                return false;
            }
            break;
        case 2:
            if (!document.querySelector('input[name="diet"]:checked')) {
                showError('Please select your diet type.');
                return false;
            }
            break;
        case 3:
            if (!document.getElementById('agreement2').checked) {
                showError('Please agree to the terms to continue.');
                return false;
            }
            break;
        case 4:
            const selectedGroceries = document.querySelectorAll('#groceryList input[type="checkbox"]:checked');
            if (selectedGroceries.length !== 11) {
                //showError(`Please select exactly 10 items. Currently selected: ${selectedGroceries.length}`);
                showError(`Please select 10 items (Cooking Oil is added automatically). Selected: ${selectedGroceries - 1}`);
                return false;
            }
            break;
    }
    return true;
}

function saveStepData(step) {
    switch(step) {
        case 2:
            surveyData.diet = document.querySelector('input[name="diet"]:checked').value;
            break;
        /*case 4:
            surveyData.groceries = Array.from(document.querySelectorAll('#groceryList input[type="checkbox"]:checked'))
                .map(cb => cb.value);
            surveyData.basketCost = 100; // Placeholder cost
            break;*/
        /*case 4:
            surveyData.groceries = Array.from(
                document.querySelectorAll('#groceryList input[type="checkbox"]:checked')
            ).map(cb => cb.value);

            const selectedItems = document.querySelectorAll('#groceryList input[type="checkbox"]:checked');
            let total = 0;
            selectedItems.forEach(cb => {
                total += parseFloat(cb.dataset.price);
            });

            surveyData.basketCost = total;
            break;*/
            case 4:
                const selectedItems = document.querySelectorAll('#groceryList input[type="checkbox"]:checked');
                
                surveyData.groceries = Array.from(selectedItems).map(cb => ({
                    id: cb.id.replace("grocery_", ""),
                    name: cb.value,
                    unit_price: parseFloat(cb.dataset.unitPrice),
                    quantity: parseInt(cb.dataset.qty),
                    total_price: parseFloat(cb.dataset.price)
                }));

                let total = 0;
                selectedItems.forEach(cb => {
                    total += parseFloat(cb.dataset.price);
                });

                //surveyData.basketCost = total;
                surveyData.basketCost = parseFloat(total.toFixed(2)); // FIXED

            break;

        case 5:
            surveyData.lunch = parseInt(document.querySelector('input[name="lunch"]:checked').value);
            break;
        case 6:
            surveyData.transport = parseInt(document.querySelector('input[name="transport"]:checked').value);
            break;
        case 7:
            surveyData.activities = parseInt(document.querySelector('input[name="activities"]:checked').value);
            break;
        case 8:
            surveyData.restaurants = parseInt(document.querySelector('input[name="restaurants"]:checked').value);
            break;
    }
}

/*function populateGroceries() {
    const diet = surveyData.diet;
    const groceryList = document.getElementById('groceryList');
    groceryList.innerHTML = '';

    groceryItems[diet].forEach(item => {
        const div = document.createElement('div');
        div.className = 'checkbox-option';
        div.innerHTML = `
            <input type="checkbox" id="grocery_${item}" value="${item}">
            <label for="grocery_${item}">${item}</label>
        `;
        
        const checkbox = div.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', function() {
            updateGrocerySelection();
            div.classList.toggle('selected', this.checked);
        });

        groceryList.appendChild(div);
    });
}*/
function populateGroceries() {
    const diet = surveyData.diet;
    const groceryList = document.getElementById('groceryList');
    groceryList.innerHTML = '';

    fetch(`/api/groceries/${diet}`)
        .then(response => response.json())
        .then(items => {

            items.forEach(item => {

                // ✅ Detect cooking oil (auto select + cannot uncheck)
                if (item.product_name.toLowerCase().includes("cooking oil")) {

                    const div = document.createElement('div');
                    div.className = 'checkbox-option selected';
                    
                    div.innerHTML = `
                        <input 
                            type="checkbox"
                            id="grocery_${item.id}"
                            value="${item.product_name}"
                            data-price="${(item.price * item.quantity).toFixed(2)}"
                            data-unit-price="${item.price}"
                            data-qty="${item.quantity}"
                            checked
                            disabled   
                            style="float: left; margin-top: 5px;"
                        >
                        <label style="padding-left: 30px; display: block;" for="grocery_${item.id}">
                            ${item.product_name} 
                            <span style="color:green; display:block; font-weight:bold;">${item.price} Zed × ${item.quantity} = ${(item.price * item.quantity).toFixed(2)} Zed</span>
                            <span style="color:green; font-weight:bold;">(Auto-included)</span>
                        </label>
                    `;

                    groceryList.appendChild(div);

                    // Save oil details for calculation
                    surveyData.oilItem = {
                        name: item.product_name,
                        price: item.price,
                        qty: item.quantity,
                        total: (item.price * item.quantity).toFixed(2)
                    };

                    return; // skip normal checkbox logic
                }

                // Normal items (user selectable)
                const div = document.createElement('div');
                div.className = 'checkbox-option';

                div.innerHTML = `
                    <input 
                        type="checkbox" 
                        id="grocery_${item.id}" 
                        value="${item.product_name}" 
                        data-price="${(item.price * item.quantity).toFixed(2)}"
                        data-unit-price="${item.price}"
                        data-qty="${item.quantity}"
                          style="float: left; margin-top: 5px;"
                    >
                    <label style="padding-left: 30px; display: block;" for="grocery_${item.id}">
                        ${item.product_name} 
                          <span style="color:green; display:block; font-weight:bold;">${item.price} Zed × ${item.quantity} = ${(item.price * item.quantity).toFixed(2)} Zed</span>
                    </label>
                `;

                const checkbox = div.querySelector('input[type="checkbox"]');
                checkbox.addEventListener('change', function() {
                    updateGrocerySelection();
                    div.classList.toggle('selected', this.checked);
                });

                groceryList.appendChild(div);
            });

            // Update selected count because oil is already selected
            updateGrocerySelection();
        })
        .catch(error => console.error('Error fetching groceries:', error));
}


/*function updateGrocerySelection() {
    const selected = document.querySelectorAll('#groceryList input[type="checkbox"]:checked').length;
    document.getElementById('selectedCount').textContent = selected;
    
    const nextBtn = document.getElementById('step4Next');
    nextBtn.disabled = selected !== 10;

    // Disable unchecked checkboxes if 10 are selected
    if (selected === 10) {
        document.querySelectorAll('#groceryList input[type="checkbox"]:not(:checked)').forEach(cb => {
            cb.disabled = true;
            cb.closest('.checkbox-option').style.opacity = '0.5';
        });
    } else {
        document.querySelectorAll('#groceryList input[type="checkbox"]').forEach(cb => {
            cb.disabled = false;
            cb.closest('.checkbox-option').style.opacity = '1';
        });
    }
}*/
/*function updateGrocerySelection() {
    const count = document.querySelectorAll('#groceryList input[type="checkbox"]:checked').length;
    const userCount = count - 1; // exclude cooking oil

    document.getElementById('selectedCount').textContent = userCount;

    const nextBtn = document.getElementById('step4Next');
    nextBtn.disabled = (count !== 11);

    // Disable other checkboxes when user has selected 10 items
    if (userCount === 10) {
        document.querySelectorAll('#groceryList input[type="checkbox"]:not(:checked)').forEach(cb => {
            cb.disabled = true;
            cb.closest('.checkbox-option').style.opacity = '0.5';
        });
    } else {
        document.querySelectorAll('#groceryList input[type="checkbox"]').forEach(cb => {
            if (!cb.disabled) { // cooking oil stays disabled
                cb.disabled = false;
                cb.closest('.checkbox-option').style.opacity = '1';
            }
        });
    }
}*/
function updateGrocerySelection() {
    const allCheckboxes = document.querySelectorAll('#groceryList input[type="checkbox"]');
    const checked = document.querySelectorAll('#groceryList input[type="checkbox"]:checked');

    const totalCount = checked.length;      // includes oil
    const userCount = totalCount - 1;       // exclude oil

    document.getElementById('selectedCount').textContent = userCount;

    const nextBtn = document.getElementById('step4Next');
    nextBtn.disabled = (totalCount !== 11); // 10 + oil

    allCheckboxes.forEach(cb => {
        const isOil = cb.disabled && cb.checked; // cooking oil only

        if (isOil) return; // never touch cooking oil

        if (userCount >= 10 && !cb.checked) {
            cb.disabled = true;
            cb.closest('.checkbox-option').style.opacity = '0.5';
        } else {
            cb.disabled = false;
            cb.closest('.checkbox-option').style.opacity = '1';
        }
    });
}

function showResults() {
    // Save last step data
    saveStepData(8);

    // Calculate totals
    const basketCost = surveyData.basketCost || 100;
    const lunchCost = surveyData.lunch || 0;
    const transportCost = surveyData.transport || 0;
    const activitiesCost = surveyData.activities || 0;
    const restaurantsCost = surveyData.restaurants || 0;
    const totalCost = basketCost + lunchCost + transportCost + activitiesCost + restaurantsCost;

    // Update results display
    /*document.getElementById('basketCost').textContent = `${basketCost} Zed`;
    document.getElementById('lunchCost').textContent = `${lunchCost} Zed`;
    document.getElementById('transportCost').textContent = `${transportCost} Zed`;
    document.getElementById('activitiesCost').textContent = `${activitiesCost} Zed`;
    document.getElementById('restaurantsCost').textContent = `${restaurantsCost} Zed`;
    document.getElementById('totalCost').textContent = `${totalCost} Zed`;*/
    const formatZed = value => `${Number(value).toFixed(2)} Zed`;

    document.getElementById('basketCost').textContent = formatZed(basketCost);
    document.getElementById('lunchCost').textContent = formatZed(lunchCost);
    document.getElementById('transportCost').textContent = formatZed(transportCost);
    document.getElementById('activitiesCost').textContent = formatZed(activitiesCost);
    document.getElementById('restaurantsCost').textContent = formatZed(restaurantsCost);
    document.getElementById('totalCost').textContent = formatZed(totalCost);


    // Store results
    surveyData.totalCost = totalCost;

    // Show results
    document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
    currentStep = 9;
    document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
    updateProgress();
    window.scrollTo(0, 0);

    // Here you would typically send the data to your server
    console.log('Survey Data:', surveyData);
    //Store survey data in local storage
    fetch('/profile/storesurvey', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(surveyData)
})
/*.then(res => res.json())
.then(data => {
    console.log('Server Response:', data);
})
.catch(err => console.error('Error saving survey:', err));*/
.then(async res => {
    const data = await res.json();

    // ❌ If insufficient balance or any server error
    /*if (!res.ok || data.status === false) {
        alert(data.message); // ✅ SHOW BACKEND MESSAGE
        return;
    }

    // ✅ Success
    alert(data.message);*/
    if (!res.ok || data.status === false) {

    Swal.fire({
        icon: 'error',
        title: 'Survey Failed',
        text: data.message,
        confirmButtonColor: '#dc3545'
    });

    return;
}

Swal.fire({
    icon: 'success',
    title: 'Survey Completed',
    text: data.message,
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false
});

})
.catch(error => {
    alert('Something went wrong. Please try again.');
    console.error(error);
});

    //Store survey data in local storage
}

function restartSurvey() {
    if (confirm('Are you sure you want to start a new survey? This will clear your current responses.')) {
        location.reload();
    }
}

function updateProgress() {
    const progressBar = document.getElementById('progressBar');
    if (!progressBar) return;
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressBar.style.width = progress + '%';
}

function showError(message) {
    const errorMsg = document.getElementById('errorMessage');
    errorMsg.textContent = message;
    errorMsg.style.display = 'block';
    window.scrollTo(0, 0);
}