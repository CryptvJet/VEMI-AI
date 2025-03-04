import spacy

# Load spaCy model
nlp = spacy.load("en_core_web_sm")

def generate_response(query, search_results):
    doc = nlp(query)
    response = f"Based on your query '{query}', here are some results:\n\n"
    
    for result in search_results:
        response += f"{result['title']}\n{result['description']}\n{result['link']}\n\n"
    
    return response

# Example usage
results = google_results + bing_results
response = generate_response(query, results)
print(response)