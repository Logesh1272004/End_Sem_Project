<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="width=device-width, initial-scale=1.0">
    <title>Diet Plan Generator</title>
    <link rel="stylesheet" href="../css/diet-plan.css">
    <link rel="icon" href="/path/to/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php
    session_start();
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" || $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    include("../connection.php");

    $sqlmain = "select * from patient where pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch = $userrow->fetch_assoc();

    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];
    ?>

    <div class="container">
        <div class="meal-planner">
            <h1>Automatic Meal Planner</h1>
            <h3>Use Our Meal Plan Generator to Create Free Diet Plans for Weight Loss, Weight Gain or Simply for Healthy Meal Ideas.</h3>

            <!-- Create a flexbox container for both the meal planner settings and the meal plan result -->
            <div class="meal-planner-content">
                <!-- Meal Planner Settings -->
                <div class="meal-planner-settings">
                    <div class="calories-section">
                        <div class="calories">
                            <span>Calories</span>
                            <input type="number" id="caloriesInput" class="calories-input" value="0" readonly> 
                            <span>kcal</span>
                        </div>
                        <div class="calculator-section">
                            <span>Not sure?</span>
                            <button class="calculator-btn" onclick="openModal()">Calculator</button>
                        </div>
                    </div>

                    <div class="activity-level-section">
                        <span>Activity Level:</span>
                        <select id="activityLevel" class="diet-type-dropdown">
                            <option value="1.55">Moderately Active</option>
                            <option value="1.725">Very Active</option>
                            <option value="1.9">Extra Active</option>
                        </select>
                    </div>

                    <div class="diet-type-section">
                        <span>Disease:</span>
                        <select id="disease" class="diet-type-dropdown">
                            <option>Diabetes</option>
                            <option>Cholesterol</option>
                            <option>PCOD</option>
                        </select>
                    </div>

                    <div class="dietary-preferences-section">
                        <span>Purpose</span>
                        <select id="purpose" class="dietary-preferences-dropdown">
                            <option>Weight Loss</option>
                            <option>Weight Gain</option>
                        </select>
                    </div>
                </div>

                <!-- Meal Plan Result -->
                <div id="meal-plan-result"></div>
            </div>

            <div class="generate-button">
                <button class="create-plan-btn" onclick="createMealPlan()">Create Meal Plan</button>
            </div>
        </div>
    </div>

    <!-- Modal to collect input for BMR calculation -->
    <div id="inputModal" class="modal">
        <div class="modal-content">
            <h2>Enter Your Information</h2>
            <label for="gender">Gender:</label>
            <select id="gender">
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>

            <label for="weight">Weight (kg):</label>
            <input type="number" id="weight" placeholder="Enter weight">

            <label for="height">Height (cm):</label>
            <input type="number" id="height" placeholder="Enter height">

            <label for="age">Age:</label>
            <input type="number" id="age" placeholder="Enter age">

            <button onclick="calculateCalories()">Submit</button>
        </div>
    </div>

    <script>
        // Open modal for input
        function openModal() {
            document.getElementById('inputModal').style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('inputModal').style.display = 'none';
        }

        // JavaScript function to calculate BMR and then calories
        function calculateCalories() {
            const weight = document.getElementById('weight').value;
            const height = document.getElementById('height').value;
            const age = document.getElementById('age').value;
            const gender = document.getElementById('gender').value;
            const activityLevel = document.getElementById("activityLevel").value;

            let BMR;
            if (gender === 'male') {
                BMR = 88.362 + (13.397 * weight) + (4.799 * height) - (5.677 * age);
            } else {
                BMR = 447.593 + (9.247 * weight) + (3.098 * height) - (4.330 * age);
            }

            const totalCalories = BMR * activityLevel;
            document.getElementById("caloriesInput").value = Math.round(totalCalories);
            closeModal();
        }

        // Create meal plan by sending data to the backend (Flask API)
        function createMealPlan() {
            const gender = document.getElementById('gender').value;
            const weight = document.getElementById('weight').value;
            const height = document.getElementById('height').value;
            const age = document.getElementById('age').value;
            const activityLevel = document.getElementById("activityLevel").value;
            const disease = document.getElementById("disease").value;
            const purpose = document.getElementById("purpose").value;

            fetch('http://127.0.0.1:5000/recommend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    gender: gender,
                    weight: weight,
                    height: height,
                    age: age,
                    activity_level: activityLevel,
                    disease: disease,
                    purpose: purpose
                }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Meal Plan:', data);

                // Display meal plan in the HTML
                displayMealPlan(data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }

        // Function to display meal plan
        function displayMealPlan(data) {
            const mealPlanResult = document.getElementById('meal-plan-result');
            mealPlanResult.innerHTML = `
                <h3>Meal Plan</h3>
                <p><strong>Calories:</strong> ${data.calories} kcal</p>
                <p><strong>Breakfast:</strong> ${data.breakfast.map(item => item.recipe).join(", ")}</p>
                <p><strong>Lunch:</strong> ${data.lunch.map(item => item.recipe).join(", ")}</p>
                <p><strong>Dinner:</strong> ${data.dinner.map(item => item.recipe).join(", ")}</p>
            `;
        }
    </script>

    <style>
        /* Flexbox Container */
        .meal-planner-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        /* Meal Planner Settings */
        .meal-planner-settings {
            width: 60%;
        }

        /* Meal Plan Result */
        #meal-plan-result {
            background-color: #f8f9fa;
            padding: 20px;
            margin-left: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 35%;
        }

        #meal-plan-result h3 {
            color: #28a745;
            font-size: 1.5em;
            margin-bottom: 15px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }

        #meal-plan-result p {
            font-size: 1.1em;
            line-height: 1.6em;
            color: #333;
        }

        #meal-plan-result strong {
            color: #28a745;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
            text-align: center;
        }

        label {
            margin-top: 10px;
            display: block;
        }

        input, select {
            margin-bottom: 15px;
            padding: 8px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #218838;
        }
    </style>

</body>
</html>
