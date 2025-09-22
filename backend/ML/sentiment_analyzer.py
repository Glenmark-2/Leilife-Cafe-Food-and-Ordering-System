import sys, json, re, torch
from transformers import AutoTokenizer, AutoModelForSequenceClassification
from spellchecker import SpellChecker  # pip install pyspellchecker

MODEL_NAME = "dost-asti/RoBERTa-tl-sentiment-analysis"
tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSequenceClassification.from_pretrained(MODEL_NAME)
model.eval()
device = torch.device("cpu")
model.to(device)

spell = SpellChecker(language=None)  # None for mixed language (Fil/Eng)

def clean_text(text: str) -> str:
    text = text.lower().strip()
    text = re.sub(r"http\S+|www\S+", "", text)
    text = re.sub(r"@\w+|#\w+", "", text)
    text = re.sub(r"\s+", " ", text).strip()
    return text

def correct_typos(text: str) -> str:
    corrected_words = []
    for word in text.split():
        # Only correct words longer than 2 characters to avoid messing up "is", "not", etc.
        if len(word) > 2:
            corrected = spell.correction(word)
            corrected_words.append(corrected if corrected else word)
        else:
            corrected_words.append(word)
    return " ".join(corrected_words)

def handle_negation(text: str) -> str:
    # Simple approach: prepend "NEG_" to words after "not" for the model to catch negation
    words = text.split()
    new_words = []
    negate = False
    for w in words:
        if w in {"not", "hindi"}:  # Add more negation words if needed
            negate = True
            new_words.append(w)
        elif negate:
            new_words.append("NEG_" + w)
            negate = False
        else:
            new_words.append(w)
    return " ".join(new_words)

def analyze_sentiment(text: str):
    cleaned = clean_text(text)
    corrected = correct_typos(cleaned)
    neg_handled = handle_negation(corrected)
    
    inputs = tokenizer(neg_handled, return_tensors="pt", truncation=True, padding=True, max_length=128)
    with torch.no_grad():
        outputs = model(**inputs)
        probs = torch.softmax(outputs.logits, dim=-1).cpu().numpy()[0]
        idx = int(probs.argmax())
    id2label = {0: "Negative", 1: "Positive", 2: "Neutral"}
    return {"label": id2label[idx], "score": float(probs[idx])}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)
    filepath = sys.argv[1]
    with open(filepath, "r", encoding="utf-8") as f:
        text = f.read().strip()
    result = analyze_sentiment(text)
    print(json.dumps(result))
