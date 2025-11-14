<?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Itinerary</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="../assets/mvitour_logo.ico" type="image/x-icon">
    <style>

        .header {
            background-color: #007bff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999; /* Increased to be higher than modal */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header a {
            text-decoration: none;
            color: white;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
        }

        .header a:hover {
            text-decoration: underline;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .profile-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 1px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .profile-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 10px;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #f8f9fa;
            color: #007bff;
            transform: translateX(5px);
        }

        .dropdown-content.show {
            display: block;
        }

        /* Arrow pointer for dropdown */
        .dropdown-content::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 20px;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid white;
        }

        /* Add background styling */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-image: url('../assets/vigan.jpg'); /* Update this path to your image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Roboto', Arial, sans-serif;
            position: relative;
        }

        /* Add an overlay to ensure text readability */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        /* Modal styling */
        .modal {
            display: block; /* Modal is displayed by default */
            position: fixed;
            z-index: 998;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            margin-top: 20px;
        }

        .modal-content {
            background-color: #fff;
            margin-top: 80px; 
            margin: 5% auto; /* Reduced from 10% to show more content */
            padding: 30px;
            border-radius: 15px;
            width: 60%; /* Increased from 50% */
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-image: linear-gradient(45deg, #007bff, #00bfff) 1;
            background: linear-gradient(to bottom, #ffffff, #f8f9fa);
            position: relative;
            overflow: hidden;

        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #007bff, #00bfff);
        }

        .modal-content::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, #007bff20, #00bfff20);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            z-index: 0;
        }

        .modal-content h2 {
            text-align: center;
            font-size: 2.5em;
            color: #333;
            font-family: 'Playfair Display', serif;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .modal-content h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, #007bff, #00bfff);
            border-radius: 2px;
        }

        /* Step form styling */
        form .step {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 2px solid #007bff;
        }

        form .step.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }

        .navigation-buttons {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            position: relative;
            padding-top: 20px;
            margin-top: 30px;
            border-top: 1px solid #eee;
        }

        .navigation-buttons .btn {
            flex: 1;
            text-align: center;
            max-width: 150px;
        }

        .navigation-buttons.both-buttons {
            justify-content: space-between;
        }

        /* Center Align Accommodation Buttons */
        .accommodation-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px; /* Space between buttons */
            margin-top: 15px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: 2px solid #007bff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: white;
            color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        /* General Input Container */
        .input-container {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        /* Budget Input Container */
        .budget-input-container {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        /* Centered Step Labels */
        form .step label {
            text-align: center;
            font-size: 1.8rem;
            font-family: 'Playfair Display', serif;
            color: #333;
            margin-bottom: 3px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        form .step label::before {
            content: '';
            width: 40px;
            height: 3px;
            background: linear-gradient(to right, #007bff, #00bfff);
            border-radius: 2px;
        }

        form .step label::after {
            content: '';
            width: 40px;
            height: 3px;
            background: linear-gradient(to left, #007bff, #00bfff);
            border-radius: 2px;
        }

        /* Optional: Adjust the modal content for better spacing */
        form .step {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Date picker styling */
        .date-container {
            display: flex;
            justify-content: center;
            gap: 50px; /* Increased gap between date inputs */
            margin: 30px auto; /* Added vertical margin */
            width: 100%;
            max-width: 600px; /* Control the overall width */
        }

        .date-input-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .date-input-group label {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }


        .duration-display {
            text-align: center;
            font-size: 1.4rem;
            color: #007bff;
            margin-top: 20px;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
        }

        /* Set minimum date width for better display */
        input[type="date"] {
            min-width: 200px;
        }

        .multi-select-option i {
            margin-right: 8px;
        }

        .multi-select-option {
            padding: 12px 20px;
            display: inline-flex;
            align-items: center;
        }

        .multi-select-option, .destination-option, .accommodation-option, .button-option {
            padding: 12px 24px;
            background-color: white;
            border: 2px solid #007bff;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .multi-select-option.selected, 
        .destination-option.selected, 
        .accommodation-option.selected, 
        .button-option.selected {
            background-color: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .multi-select-option:hover, 
        .destination-option:hover, 
        .accommodation-option:hover, 
        .button-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .multi-select-group, .destination-group, .accommodation-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
            justify-content: center;
            padding: 10px;
        }

        .input-field, .budget-input, .date-input {
            width: 60%;
            padding: 15px;
            font-size: 1.1rem;
            border: 2px solid #007bff;
            border-radius: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .input-field:focus, .budget-input:focus, .date-input:focus {
            border-color: #0056b3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            outline: none;
            transform: translateY(-2px);
        }

        .step-indicator {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9rem;
            color: #007bff;
            background: white;
            padding: 5px 15px;
            border-radius: 15px;
            border: 1px solid #007bff;
        }
    </style>
</head>
<body>

    <!-- Fixed Header Section -->
    <header class="header">
        <a href="homepage.php" class="logo">
            <img src="../assets/mvitour_logo.png" alt="MViTour Logo">
            MViTour
        </a>
        <div class="header-nav">
            <a href="destination_listing.php" class="nav-link">
                <i class="fas fa-list-ul"></i> Destinations
            </a>
            <?php if ($isLoggedIn): ?>
                <div class="profile-dropdown">
                    <button type="button" class="profile-btn" aria-label="Profile menu">
                        <i class="fas fa-user"></i>
                    </button>
                    <div class="dropdown-content" aria-label="Profile options">
                        <a href="edit_preferences.php">
                            <i class="fas fa-cog"></i> Edit Preferences
                        </a>
                        <a href="logout_useracc.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login_useracc.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div id="itineraryModal" class="modal">
        <div class="modal-content">
            <h2>Create Your Itinerary</h2>

            <div class="step-indicator">
                Step <span id="currentStepNumber">1</span> of 9
            </div>

            <form action="itinerary_results.php" method="post">
                <!-- Step 1 -->
                <div class="step active">
                    <label for="people">How many people are traveling?</label>
                    <div class="input-container">
                        <input type="number" id="people" name="people" min="1" class="input-field" required>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step">
                    <label>What is your destination?</label>
                    <div class="destination-group" id="destinationGroup">
                        <div class="destination-option" data-value="Vigan">Vigan</div>
                        <div class="destination-option" data-value="Bantay">Bantay</div>
                        <div class="destination-option" data-value="Santa Catalina">Santa Catalina</div>
                        <div class="destination-option" data-value="San Vicente">San Vicente</div>
                        <div class="destination-option" data-value="Caoayan">Caoayan</div>
                    </div>
                    <input type="hidden" id="destination" name="destination">
                </div>

                <!-- Step 3 -->
                <div class="step">
                    <label>When is your trip?</label>
                    <div class="date-container">
                        <div class="date-input-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="date-input" required>
                        </div>
                        <div class="date-input-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="date-input" required>
                        </div>
                    </div>
                    <div class="duration-display">
                        Trip Duration: <span id="trip_duration">0</span> days
                    </div>
                    <input type="hidden" id="duration" name="duration">
                </div>

                <!-- Step 4 -->
                <div class="step">
                    <label for="budget">What is your budget for the trip?</label>
                    <div class="budget-input-container">
                        <input type="number" id="budget" name="budget" min="0" class="budget-input" required>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="step">
                    <label>What type of activities do you enjoy?</label>
                    <div class="multi-select-group" id="activitiesGroup">
                        <div class="multi-select-option" data-value="Cultural"><i class="fas fa-masks-theater"></i> Cultural</div>
                        <div class="multi-select-option" data-value="Historical"><i class="fas fa-landmark"></i> Historical</div>
                        <div class="multi-select-option" data-value="Adventure"><i class="fas fa-compass"></i> Adventure</div>
                        <div class="multi-select-option" data-value="Outdoor and Nature"><i class="fas fa-leaf"></i> Outdoor and Nature</div>
                        <div class="multi-select-option" data-value="Relaxation"><i class="fas fa-spa"></i> Relaxation</div>
                        <div class="multi-select-option" data-value="Educational"><i class="fas fa-book-open"></i> Educational</div>
                        <div class="multi-select-option" data-value="Family-Oriented"><i class="fas fa-users"></i> Family-Oriented</div>
                        <div class="multi-select-option" data-value="Sports and Fitness"><i class="fas fa-person-running"></i> Sports and Fitness</div>
                    </div>
                    <input type="hidden" id="activities" name="activities">
                </div>

                <!-- Step 6 -->
                <div class="step">
                    <label>What kind of sights are you most interested in?</label>
                    <div class="multi-select-group" id="sightsGroup"></div>
                    <input type="hidden" id="sights" name="sights">
                </div>

                <!-- Step 7 -->
                <div class="step">
                    <label>Would you like to include dining options after destinations?</label>
                    <div class="accommodation-group" id="diningOptions">
                        <div class="button-option" data-value="yes">Yes</div>
                        <div class="button-option" data-value="no">No</div>
                    </div>
                    <input type="hidden" id="include_dining" name="include_dining">
                </div>

                <!-- Step 8 -->
                <div class="step">
                    <label>Are you looking for accommodations for your stay?</label>
                    <div class="accommodation-group" id="accommodationOptions">
                        <div class="button-option" data-value="yes">Yes</div>
                        <div class="button-option" data-value="no">No</div>
                    </div>
                    <input type="hidden" id="need_accommodation" name="need_accommodation">
                </div>

                <!-- Step 9 -->
                <div class="step">
                    <label>What type of accommodation do you prefer?</label>
                    <div class="accommodation-group" id="accommodationGroup">
                        <div class="accommodation-option" data-value="Hotel"><i class="fas fa-hotel"></i> Hotel</div>
                        <div class="accommodation-option" data-value="Resort"><i class="fas fa-umbrella-beach"></i> Resort</div>
                        <div class="accommodation-option" data-value="Inn"><i class="fas fa-bed"></i> Inn</div>
                        <div class="accommodation-option" data-value="Transient House"><i class="fas fa-house"></i> Transient House</div>
                        <div class="accommodation-option" data-value="Apartelle"><i class="fas fa-building"></i> Apartelle</div>
                    </div>
                    <input type="hidden" id="accommodation" name="accommodation">
                </div>  

                <!-- Navigation Buttons -->
                <div class="navigation-buttons">
                    <button type="button" id="prevBtn" class="btn" style="display:none;">Previous</button>
                    <button type="button" id="nextBtn" class="btn">Next</button>
                    <button type="submit" id="submitBtn" class="btn" style="display:none;">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Core variables
        const sightsGroup = document.getElementById("sightsGroup");
        const accommodationOptions = document.getElementById("accommodationOptions");
        const needAccommodationInput = document.getElementById("need_accommodation");
        const steps = document.querySelectorAll('.step');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const tripDurationDisplay = document.getElementById('trip_duration');
        const durationInput = document.getElementById('duration');
        const activitiesGroup = document.getElementById("activitiesGroup");
        const destinationGroup = document.getElementById('destinationGroup');
        const destinationInput = document.getElementById('destination');
        const accommodationGroup = document.getElementById('accommodationGroup');
        const accommodationInput = document.getElementById('accommodation');
        const diningOptions = document.getElementById('diningOptions');
        const includeDiningInput = document.getElementById('include_dining');

        let currentStep = 0;
        let selectedActivities = [];

        // Validation states
        let validations = {
            step1: false,
            step2: false,
            step3: false,
            step4: false,
            step5: false,
            step6: false,
            step7: false,
            step8: false,
            step9: false
        };

        // Activity to sights mapping
        const activityToSights = {
            "Cultural": ["Religious Site", "Museum", "Structures and Buildings"],
            "Historical": ["Religious Site", "Museum", "Historical Road", "Structures and Buildings"],
            "Adventure": ["Beaches", "Falls", "Nature Trail", "Camping Ground"],
            "Outdoor and Nature": ["Beaches", "Parks", "Falls", "Nature Trail", "Camping Ground"],
            "Relaxation": ["Beaches", "Parks", "Falls", "Nature Trail"],
            "Educational": ["Museum"],
            "Family-Oriented": ["Parks", "Recreational Activities"],
            "Sports and Fitness": ["Recreational Activities"]
        };

        // Icon mapping functions
        function getCategoryIcon(category) {
            const iconMap = {
                'Religious Site': 'fa-church',
                'Museum': 'fa-landmark',
                'Historical Road': 'fa-road',
                'Structures and Buildings': 'fa-building',
                'Beaches': 'fa-umbrella-beach',
                'Parks': 'fa-tree',
                'Falls': 'fa-water',
                'Nature Trail': 'fa-mountain',
                'Camping Ground': 'fa-campground',
                'Recreational Activities': 'fa-hiking'
            };
            return iconMap[category] || 'fa-monument';
        }

        // Button visibility update function
        function updateButtonVisibility() {
            const currentStepValid = validations[`step${currentStep + 1}`];
            
            if (currentStep === 7) {
                const needAccommodation = needAccommodationInput.value === 'yes';
                nextBtn.style.display = currentStepValid && needAccommodation ? 'inline-block' : 'none';
                submitBtn.style.display = currentStepValid && !needAccommodation ? 'inline-block' : 'none';
            } else if (currentStep === steps.length - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = currentStepValid ? 'inline-block' : 'none';
            } else {
                nextBtn.style.display = currentStepValid ? 'inline-block' : 'none';
                submitBtn.style.display = 'none';
            }
        }

        // Show step function
        function showStep(index) {
            steps.forEach((step, i) => {
                step.classList.remove('active');
                if (i === index) step.classList.add('active');
            });

            prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
            updateButtonVisibility();

            const navigationButtons = document.querySelector('.navigation-buttons');
            navigationButtons.classList.toggle('both-buttons', index !== 0);

            document.getElementById('currentStepNumber').textContent = index + 1;

            // Update sights when navigating to the sights step
            if (index === 5) {
                updateSights();
            }
        }

        // Update sights function
        function updateSights() {
            sightsGroup.innerHTML = "";
            const sights = new Set();
            selectedActivities.forEach(activity => {
                if (activityToSights[activity]) {
                    activityToSights[activity].forEach(sight => sights.add(sight));
                }
            });
            
            sights.forEach(sight => {
                const div = document.createElement("div");
                div.classList.add("multi-select-option");
                div.setAttribute("data-value", sight);
                
                const icon = document.createElement("i");
                icon.className = `fas ${getCategoryIcon(sight)}`;
                
                const text = document.createTextNode(` ${sight}`);
                
                div.appendChild(icon);
                div.appendChild(text);
                sightsGroup.appendChild(div);
            });
        }

        // Date handling functions
        function updateDuration() {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                
                if (duration > 0) {
                    tripDurationDisplay.textContent = duration;
                    durationInput.value = duration;
                    validations.step3 = true;
                } else {
                    tripDurationDisplay.textContent = '0';
                    durationInput.value = '';
                    endDate.value = '';
                    validations.step3 = false;
                }
                updateButtonVisibility();
            }
        }

        // Event Listeners
        // Step 1: Number of people
        document.getElementById('people').addEventListener('input', function() {
            validations.step1 = this.value && this.value > 0;
            updateButtonVisibility();
        });

        // Step 2: Destination
        destinationGroup.addEventListener('click', (e) => {
            if (e.target.classList.contains('destination-option')) {
                Array.from(destinationGroup.children).forEach(btn => btn.classList.remove('selected'));
                e.target.classList.add('selected');
                destinationInput.value = e.target.getAttribute('data-value');
                validations.step2 = true;
                updateButtonVisibility();
            }
        });

        // Step 3: Dates
        const today = new Date().toISOString().split('T')[0];
        startDate.min = today;
        endDate.min = today;

        startDate.addEventListener('change', function() {
            endDate.min = this.value;
            updateDuration();
        });

        endDate.addEventListener('change', updateDuration);

        // Step 4: Budget
        document.getElementById('budget').addEventListener('input', function() {
            validations.step4 = this.value && this.value > 0;
            updateButtonVisibility();
        });

        // Step 5: Activities
        activitiesGroup.addEventListener('click', (e) => {
            if (e.target.classList.contains('multi-select-option')) {
                e.target.classList.toggle('selected');
                selectedActivities = Array.from(activitiesGroup.querySelectorAll('.selected'))
                    .map(el => el.getAttribute('data-value'));
                document.getElementById('activities').value = selectedActivities.join(',');
                validations.step5 = selectedActivities.length > 0;
                updateButtonVisibility();
            }
        });

        // Step 6: Sights
        sightsGroup.addEventListener('click', (e) => {
            if (e.target.classList.contains('multi-select-option')) {
                e.target.classList.toggle('selected');
                const selectedSights = Array.from(sightsGroup.querySelectorAll('.selected'))
                    .map(el => el.getAttribute('data-value'));
                document.getElementById('sights').value = selectedSights.join(',');
                validations.step6 = selectedSights.length > 0;
                updateButtonVisibility();
            }
        });

        // Step 7: Dining Options
        diningOptions.addEventListener('click', (e) => {
            if (e.target.classList.contains('button-option')) {
                Array.from(diningOptions.children).forEach(btn => btn.classList.remove('selected'));
                e.target.classList.add('selected');
                includeDiningInput.value = e.target.getAttribute('data-value');
                validations.step7 = true;
                updateButtonVisibility();
            }
        });

        // Step 8: Accommodation Need
        accommodationOptions.addEventListener('click', (e) => {
            if (e.target.classList.contains('button-option')) {
                Array.from(accommodationOptions.children).forEach(btn => btn.classList.remove('selected'));
                e.target.classList.add('selected');
                needAccommodationInput.value = e.target.getAttribute('data-value');
                validations.step8 = true;
                updateButtonVisibility();
            }
        });

        // Step 9: Accommodation Type
        accommodationGroup.addEventListener('click', (e) => {
            if (e.target.classList.contains('accommodation-option')) {
                Array.from(accommodationGroup.children).forEach(btn => btn.classList.remove('selected'));
                e.target.classList.add('selected');
                accommodationInput.value = e.target.getAttribute('data-value');
                validations.step9 = true;
                updateButtonVisibility();
            }
        });

        // Navigation button event listeners
        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        });

        // Profile dropdown functionality
        const profileBtn = document.querySelector('.profile-btn');
        const dropdownContent = document.querySelector('.dropdown-content');

        if (profileBtn && dropdownContent) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownContent.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target)) {
                    dropdownContent.classList.remove('show');
                }
            });
        }

        // Initialize first step
        showStep(currentStep);
    </script>
</body>
</html>
