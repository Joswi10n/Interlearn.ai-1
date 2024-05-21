from flask import Flask, request,jsonify,render_template
import torch
from llama_index.core import SimpleDirectoryReader, VectorStoreIndex
from langchain.callbacks import StreamingStdOutCallbackHandler
from langchain.llms import CTransformers
from llama_index.embeddings.huggingface import HuggingFaceEmbedding
import os 
from llama_index.core.node_parser import SentenceSplitter
import sys
from gtts import gTTS 
from pydub import AudioSegment
import numpy as np
import imageio
import subprocess
from flask_cors import CORS
import re
from langchain_community.chat_models import ChatOpenAI

app = Flask(__name__,static_folder="static")
CORS(app)  # Enable CORS for all routes


# Define global variable for query engine
topics_list=[]
query_engine = None
chat_engine = None
cp =" "
count=0
count1=0
count2=-1
qa={}
qa1={}
qa2={}

def topics_route(chapter_path):
    global cp,query_engine,chat_engine,topics_list
    if cp != chapter_path:
        
        print("Chapter Path:", chapter_path)
        device = torch.device("cpu")
        #device = torch.device("cuda:0" if torch.cuda.is_available() else "cpu")
        documents = SimpleDirectoryReader(chapter_path).load_data()
        i = len(documents)
        documents = [doc for doc in documents if int(doc.metadata.get("page_label")) not in [i, i - 1]]
        llm = CTransformers(model="TheBloke/Llama-2-7B-Chat-GGML",
                    streaming=True, callbacks=[StreamingStdOutCallbackHandler()],model_type ="llama"
                    ,context_length=3000, device=device,config={'max_new_tokens': 3000,'context_length':3000, 'temperature': 0.2})
        index = VectorStoreIndex.from_documents(documents,streaming=True,embed_model=HuggingFaceEmbedding(),transformations=[SentenceSplitter(chunk_size=550,chunk_overlap=20)])
        query_engine = index.as_query_engine(llm=llm, device=device)
        chat_engine = index.as_chat_engine(llm=llm, device=device)
        cp=chapter_path
        topics_list=[]
    if topics_list==[]:
        response = query_engine.query("Identify and list the 10 most important topics(each topic as 3 words at max ) covered in this chapter, excluding any content presented in question format as 1. Topic 1 2.Topic 2 such that each topic in a different line with numbering")
        print(response)
        res = str(response)
        for line in res.split('\n'):
            # Check if the line starts with a number followed by a period
            if re.match(r'^\d+\.\s', line):
                # Split the line by the first occurrence of the period
                parts = line.split('. ', 1)
                if len(parts) > 1:
                    topics_list.append(parts[1])  # Take the second part after the split
            # Check if the line contains a colon
            elif ':' in line:
                parts = line.split(': ', 1)
                if len(parts) > 1:
                    topics_list.append(parts[1])  # Take the second part after the split
            # Check if the line contains a hyphen
            elif '-' in line:
                parts = line.split('- ', 1)
                if len(parts) > 1:
                    topics_list.append(parts[1])  # Take the second part after the split
            else:
                topics_list.append("")
        topics_list = topics_list[1:]
    return topics_list

import json 


@app.route('/display_topics/<path:chapter_path>')
def display_topics(chapter_path):
    topics = topics_route(chapter_path)
    return json.dumps({'topics': topics})
import os

@app.route('/topic_explanation/<topic>/<difficulty>/<path:chapter_path>')
def topic_explanation(topic, difficulty, chapter_path):
    print("Topic:", topic)
    print("Difficulty:", difficulty)
    print("Chapter Path:", chapter_path)

    if difficulty == "1":
        prompt = f"Explain very briefly the topic '{topic}' in simple difficulty based on the chapter context in 5 sentences as a paragraph exclude the characters mentioned in it , focus on technical terms more:"
    elif difficulty == "2":
        prompt = f"Explain very briefly the topic '{topic}' in average difficulty based on the chapter context in 7 sentences as a paragraph exclude the characters mentioned in it , focus on technical terms more:"
    else:
        prompt = f"Explain very briefly the topic '{topic}' in depth based on the chapter context in 10 sentences as a paragraph exclude the characters mentioned in it , focus on technical terms more:"
    
    response = query_engine.query(prompt)
    response = str(response)
    language = 'en'
    myobj = gTTS(text=str(response), lang=language, slow=False) 
    myobj.save("welcome.mp3")

    audio_file = "welcome.mp3"
    video_file = "sample.mp4"
    output_file = "output.mp4"
    print("HIIIII")
    audio = AudioSegment.from_file(audio_file)

    try:
        video_reader = imageio.get_reader(video_file)
        fps = video_reader.get_meta_data()['fps']
        frame_list = [frame for frame in video_reader]
        video_duration = len(frame_list) / fps
    except FileNotFoundError:
        return "Error: Video file not found."

    audio_duration = len(audio) / 1000  # in seconds
    loop_count = int(np.ceil(audio_duration / video_duration))
    video_frames = np.concatenate([frame_list] * loop_count)

    writer = imageio.get_writer(output_file, fps=fps)
    for frame in video_frames:
        writer.append_data(frame)
    writer.close()

    audio.export("temp_audio.wav", format="wav")
    
    # Update ffmpeg path to use absolute path
    ffmpeg_path = os.path.join(os.getcwd(), "ffmpeg", "bin", "ffmpeg.exe")
    
    combine_cmd = f'{ffmpeg_path} -y -i temp_audio.wav -i {output_file} -c:v copy -c:a aac -strict experimental templates/static/abcd.mp4'

    subprocess.run(combine_cmd, shell=True)

    return response
qa1={}
@app.route('/handle_question', methods=['POST'])
def handle_question():
    global cp, query_engine, chat_engine,qa1,count1
    data = request.json
    question = data['question']
    chapter_path = data['chapter_path']
    
    # Initialize query and chat engines if chapter path changes
    if cp != chapter_path:
        qa1={ }
        count1=0
        print("Chapter Path:", chapter_path)
        device = torch.device("cpu")
        device = torch.device("cuda:0" if torch.cuda.is_available() else "cpu")
        documents = SimpleDirectoryReader(chapter_path).load_data()
        i = len(documents)
        documents = [doc for doc in documents if int(doc.metadata.get("page_label")) not in [i, i - 1]]
        llm = CTransformers(model="TheBloke/Llama-2-7B-Chat-GGML",
                    streaming=True, callbacks=[StreamingStdOutCallbackHandler()],model_type ="llama"
                    ,context_length=3000, device=device,config={'max_new_tokens': 3000,'context_length':3000, 'temperature': 0.2})
        index = VectorStoreIndex.from_documents(documents,streaming=True,embed_model=HuggingFaceEmbedding(),transformations=[SentenceSplitter(chunk_size=550,chunk_overlap=20)])
        query_engine = index.as_query_engine(llm=llm, device=device)
        chat_engine = index.as_chat_engine(llm=llm, device=device)
        cp = chapter_path
    
    
    prompt = f"'{question}':"
    response = query_engine.query(prompt)
    response = str(response)
    count1=count1+1
    # Append question and response to the qa dictionary
    qa1[count1]=question,response
    print(qa1)
    
    return jsonify(qa1)
import assemblyai
# Replace 'YOUR_API_KEY' with your AssemblyAI API key
assemblyai.api_key = '1795b787b73a459ca85de1b24dc31c44'
import assemblyai as aai

@app.route('/save_audio', methods=['POST'])
def save_audio():
    if 'audio' in request.files:
        audio_file = request.files['audio']
        audio_path = os.path.join(app.root_path, 'static', 'new_audio.wav')
        audio_file.save(audio_path)
        return jsonify({'message': 'Audio saved successfully'}), 200
    else:
        return jsonify({'error': 'No audio file found in request'}), 400

@app.route('/transcribe_audio')
def transcribe_audio():
    aai.settings.api_key = "1795b787b73a459ca85de1b24dc31c44"
    transcriber = aai.Transcriber()
    audio_path = os.path.join(app.root_path, 'static', 'new_audio.wav')
    if os.path.exists(audio_path):
        transcript = transcriber.transcribe("static/new_audio.wav")
        os.remove(audio_path)
        
        return jsonify(transcript.text), 200
    else:
        return jsonify({'error': 'Audio file not found'}), 404

@app.route('/handle_question1', methods=['POST'])
def handle_question1():
    global cp, query_engine, chat_engine, qa2, count2
    data = request.json
    question = data['question']
    chapter_path = data['chapter_path']
    
    # Initialize query and chat engines if chapter path changes
    if cp != chapter_path:
        qa2 = {}
        count2 = -1
        print("Chapter Path:", chapter_path)
        device = torch.device("cpu")
        device = torch.device("cuda:0" if torch.cuda.is_available() else "cpu")
        documents = SimpleDirectoryReader(chapter_path).load_data()
        i = len(documents)
        documents = [doc for doc in documents if int(doc.metadata.get("page_label")) not in [i, i - 1]]
        llm = CTransformers(model="TheBloke/Llama-2-7B-Chat-GGML",
                    streaming=True, callbacks=[StreamingStdOutCallbackHandler()],model_type ="llama"
                    ,context_length=3000, device=device,config={'max_new_tokens': 3000,'context_length':3000, 'temperature': 0.2})
        index = VectorStoreIndex.from_documents(documents,streaming=True,embed_model=HuggingFaceEmbedding(),transformations=[SentenceSplitter(chunk_size=550,chunk_overlap=20)])
        query_engine = index.as_query_engine(llm=llm, device=device)
        chat_engine = index.as_chat_engine(llm=llm, device=device)
        cp = chapter_path
    
    prompt = f"{question} in short "
    response = query_engine.query(prompt)
    response = str(response)
    count2 += 1
    # Append question and response to the qa dictionary
    qa2[count2] = question, response
    print(qa2)
    
    return jsonify(qa2)

qa={}    
@app.route('/handle_chat', methods=['POST'])
def handle_chat():
    global cp, query_engine, chat_engine,qa,count
    data = request.json
    question = data['question']
    chapter_path = data['chapter_path']
    
    # Initialize query and chat engines if chapter path changes
    if cp != chapter_path:
        qa={ }
        count=0
        print("Chapter Path:", chapter_path)
        device = torch.device("cpu")
        device = torch.device("cuda:0" if torch.cuda.is_available() else "cpu")
        documents = SimpleDirectoryReader(chapter_path).load_data()
        i = len(documents)
        documents = [doc for doc in documents if int(doc.metadata.get("page_label")) not in [i, i - 1]]
        llm = CTransformers(model="TheBloke/Llama-2-7B-Chat-GGML",
                    streaming=True, callbacks=[StreamingStdOutCallbackHandler()],model_type ="llama"
                    ,context_length=3000, device=device,config={'max_new_tokens': 3000,'context_length':3000, 'temperature': 0.2})
        index = VectorStoreIndex.from_documents(documents,streaming=True,embed_model=HuggingFaceEmbedding(),transformations=[SentenceSplitter(chunk_size=550,chunk_overlap=20)])
        query_engine = index.as_query_engine(llm=llm, device=device)
        chat_engine = index.as_chat_engine(llm=llm, device=device)
        cp = chapter_path
    
    # Determine the number of sentences based on the desired response length
    response_length = data.get('response_length', 'short')  # Default to 'short' if not specified
    if response_length == 'short':
        prompt_length = 5
    elif response_length == 'medium':
        prompt_length = 10
    elif response_length == 'long':
        prompt_length = 15
    
    prompt = f"Explain the question '{question}' based on the chapter context in {prompt_length} sentences:"
    response = query_engine.query(prompt)
    response = str(response)
    count=count+1
    # Append question and response to the qa dictionary
    qa[count]=question,response
    print(qa)
    
    return jsonify(qa)



if __name__ == '__main__':
    app.run(debug=False)