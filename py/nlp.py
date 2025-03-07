import spacy
import sys
import json

# Load the spaCy model
nlp = spacy.load("en_core_web_sm")

def generate_response(user_message, search_results):
    doc = nlp(user_message)
    # Process the user message and search results to generate a response
    # For simplicity, we will just return a summary of the search results
    response = "Here are some results I found:\n"
    for result in search_results[:3]:  # Limit to top 3 results
        response += f"- {result['title']}: {result['link']}\n"
    return response

if __name__ == "__main__":
    user_message = sys.argv[1]
    search_results_json = sys.argv[2]

    # Debugging: Print the user message and search results JSON
    print(f"User Message: {user_message}")
    print(f"Search Results JSON: {search_results_json}")

    try:
        search_results = json.loads(search_results_json)
        response = generate_response(user_message, search_results)
        print(response)
    except json.JSONDecodeError as e:
        print(f"JSON Decode Error: {e}")
        print("Invalid JSON input.")