# 🎓 InterLearn.AI - AI-Powered Interactive Learning Platform

**InterLearn.AI** is an **AI-driven education platform** that integrates **Generative AI, Retrieval-Augmented Generation (RAG), and automated video generation** to provide **high-quality interactive learning for school students**. It leverages **PDF files from CBSE and ICSE chapters** to create a personalized learning experience, reducing the need for extra tuition.

## 🚀 Features

- 📚 **Personalized Learning Dashboard**  
  - Students see chapters based on their **class and syllabus** (CBSE/ICSE) upon signing in.

- 🤖 **Chat with Chapter**  
  - Students can ask **questions related to a chapter** using a **RAG-powered chatbot**.  
  - The chatbot is backed by **Llama 2.0**, integrated using **LlamaIndex and LangChain**.

- 📖 **Learn by Topic**  
  - Students can **learn chapter topics in a structured way**.  
  - The **10 most important topics** are extracted using **RAG on the chapter content**.  
  - Students select a **topic to study first** and **choose the depth of explanation**.

- 🎥 **AI-Generated Video Learning**  
  - The topic explanation is **converted into speech** using **Google TTS**.  
  - A **cartoon-style video** is generated using **FFMPEG** with **looped animations** (not lipsynced).

- 🎤 **Voice & Text-Based Doubt Resolution**  
  - Students can **ask doubts via voice or text**.  
  - Doubts are transcribed using **AssemblyAI API**.  
  - The AI system provides **contextual answers** based on the explanation.

## 🏗️ Tech Stack

| **Technology**            | **Usage**                                   |
|--------------------------|-------------------------------------------|
| **HTML, CSS, JavaScript** | Frontend UI & Styling                     |
| **PHP & Flask**          | Backend logic                             |
| **MySQL**                | Database for storing user data & chapters |
| **Llama 2.0**            | Language Model for RAG                     |
| **LangChain & LlamaIndex** | AI-based text processing & embeddings    |
| **AssemblyAI API**        | Speech-to-Text (Transcription)            |
| **Google TTS (gTTS)**     | Text-to-Speech for explanations           |
| **FFMPEG**               | Video Generation                          |

## 🛠️ Setup Instructions

### Clone the Repository
```sh
git clone https://github.com/anselthomas/InterLearn.AI.git
cd InterLearn.AI
```

### Database Setup
- Import the **SQL schema** into **MySQL**.
- Update `connect.php` with your **database credentials**.

### Run the Application
- **Backend**: Start the Flask and PHP servers.
- **Frontend**: Open the website in a browser.

## 📩 Contact
📧 **anselkthomas@gmail.com**
📧 **joswincraju2001@gmail.com**
---
