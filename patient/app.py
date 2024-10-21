from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Enable CORS to allow cross-origin requests

# Define your Python model logic here
@app.route('/recommend', methods=['POST'])
def recommend():
    data = request.json
    gender = data.get('gender')
    weight = data.get('weight')
    height = data.get('height')
    age = data.get('age')
    activity_level = data.get('activity_level')
    disease = data.get('disease')
    purpose = data.get('purpose')

    # Basic example: Calculate BMR and use it for calorie needs
    if gender == 'male':
        BMR = 88.362 + (13.397 * float(weight)) + (4.799 * float(height)) - (5.677 * float(age))
    else:
        BMR = 447.593 + (9.247 * float(weight)) + (3.098 * float(height)) - (4.330 * float(age))

    total_calories = BMR * float(activity_level)

    # Example meal plan data based on disease and purpose
    if disease == "Diabetes":
        meal_plan = {
            "breakfast": [{"recipe": "Oatmeal with berries", "calories": 300}],
            "lunch": [{"recipe": "Grilled Chicken Salad", "calories": 400}],
            "dinner": [{"recipe": "Stir-fried Vegetables with Tofu", "calories": 500}]
        }
    elif disease == "Cholesterol":
        meal_plan = {
            "breakfast": [{"recipe": "Avocado Toast", "calories": 300}],
            "lunch": [{"recipe": "Quinoa Salad", "calories": 400}],
            "dinner": [{"recipe": "Grilled Salmon", "calories": 500}]
        }
    else:  # PCOD or any other condition
        meal_plan = {
            "breakfast": [{"recipe": "Smoothie", "calories": 300}],
            "lunch": [{"recipe": "Lentil Soup", "calories": 400}],
            "dinner": [{"recipe": "Whole Grain Pasta", "calories": 500}]
        }

    # Example response with calculated calories and meal plan
    response = {
        "calories": total_calories,
        "breakfast": meal_plan["breakfast"],
        "lunch": meal_plan["lunch"],
        "dinner": meal_plan["dinner"]
    }
    
    return jsonify(response)

if __name__ == '__main__':
    app.run(debug=True)
