import requests
from bs4 import BeautifulSoup
import json

def scrape_google(query):
    url = f"https://www.google.com/search?q={query}"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"}
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, "html.parser")
    
    results = []
    for g in soup.find_all(class_='BVG0Nb'):
        title = g.find('h3').text
        link = g.find('a')['href']
        description = g.find('span', {'class': 'aCOpRe'}).text
        results.append({'title': title, 'link': link, 'description': description})
    
    return results

def scrape_bing(query):
    url = f"https://www.bing.com/search?q={query}"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3"}
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, "html.parser")
    
    results = []
    for b in soup.find_all(class_='b_algo'):
        title = b.find('h2').text
        link = b.find('a')['href']
        description = b.find('p').text
        results.append({'title': title, 'link': link, 'description': description})
    
    return results

# Combine results from Google and Bing
def scrape(query):
    google_results = scrape_google(query)
    bing_results = scrape_bing(query)
    combined_results = google_results + bing_results
    return combined_results

# Example usage
query = "Latest news on AI"
results = scrape(query)
print(json.dumps(results, indent=4))