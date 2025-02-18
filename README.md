# LaravelAPI-AI

A school asignment where there you send a request format to a LLM thru an API, than later store said requests and responses in a local database



## To run the application

Programs that you need installed:
- Ollama
- Fetch "mistral" as the LLM with Ollama commands in your CMD
- Install Insomnia
- Have PHP installed
- Have docker-desktop installed
- Have Herd (optional, this project is laravel/herd project, but if you dont got Herd, you can always convert it into a regular PHP/Laravel project)


After having the right programs installed:
- After cloning down the project, set up your .env file
- Create a .env file than copy the content from the .env.example file
- Run following commands:
  - npm install
  - composer install
  - docker-compose up -d --build

Now the php is all set up, now open up insomnia and put in the following HTTP-requests:

OBS! Make sure that all the input fields in each request can take json data.

| Name | Method | URL |
|------|--------|-----|
|Register|POST|http://LaravelApi.test/api/register|
|Login|POST|http://LaravelApi.test/api/login|
|Logout|GET|http://LaravelApi.test/api/logout|
|Chat|POST|http://LaravelApi.test/api/chat|


### Inside the Register request:

Enter in this under **Body** tag:

    {
        "name": "Your Username",
        "email": "Your email",
        "password": "Your password",
        "password_confirmation": "Your password confirmation"
    }

### Inside the Login request:

Enter in this under **Body** tag:
    
    {
        "email": "Your email",
        "password": "Your password"
    }

After you have successfully logged in, you will be granted an "accessToken",
copy that token and save it for later, you will need it.


### Inside the Logut request:

Go under the **Auth** tag,
there you will see **TOKEN**, take the accesstoken from earlier and paste it in there.
OBS, you will still need it for the next step as well.
But now you can logout with the user information you just logged in with.

### Inside the Chat Request:

Go under the **Auth** tag,
there you will see **TOKEN**, take the accesstoken from earlier and paste it in there.

Then here is too send requests to the LLM:

inside the **Body** tag:

    {
        "session_id": x,
        "message": y
    }


X represent if you want to continue a converstation with the LLM regarding previous messages, and Y represents the message you wanna sent. If you dont got a session_id, you can remove the whole session_id line if you want.

Example 1:

    {
        "message": "what is 1 + 1?"
    }

Response:

    {
	"session_id": "xxxxxxxx-xxx-xxxx-xxxx-xxxxxxxxxxxx",
	"message": "2"
    }


If I then take this session_id and put it with my next message:

    {
        "session_id": "xxxxxxxx-xxx-xxxx-xxxx-xxxxxxxxxxxx",
        "message": "what if you replace the first 1 with an 8, and use subtraction instead of addition?"
    }

Response after history:

    {
	    "session_id": "xxxxxxxx-xxx-xxxx-xxxx-xxxxxxxxxxxx",
	    "message": "8 - 1 = 7. So in this case, the result would be 7."
    }


If you ever forget your session_id and havent saved it, it will automaticlly get saved to each of your request and bot_responses together with the user_id inside the MariaDB:

Inisde the table "chat_histories" it would look something simulare too this:

|id|user_id|session_id|user_message|bot_response|timestamps|
|---|---|---|---|---|---|
|1|1|xyx|what is 1 + 1?|2|now()|
|2|1|xyx|what if you replace the first 1 with an 8, and use subtraction instead of addition?|8 - 1 = 7. So in this case, the result would be 7.|now()



And if you where to ask a follow up questions without using the propper session_id, this is response you might get:

    {
	"session_id": "xxx",
	"message": " If you replace the first \"1\" with an \"8\" and use subtraction instead of addition, the operation would be different. For example, if we have the equation 1 + 2 = 3, replacing the first \"1\" with an \"8\" would give us the equation 8 + 2, which equals 10 when using addition. However, if you change it to subtraction (8 - 2), the result would be 6. So, the original equation of 1 + 2 = 3 does not equal the modified equation 8 - 2 when using subtraction."
    }