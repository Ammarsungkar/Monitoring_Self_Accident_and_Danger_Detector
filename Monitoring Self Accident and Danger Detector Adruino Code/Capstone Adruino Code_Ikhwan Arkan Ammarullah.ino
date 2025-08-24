#include <SoftwareSerial.h>
#include <TinyGPS++.h>
#include <WiFi.h>
#include <Firebase_ESP_Client.h>

// Wi-Fi credentials
#define WIFI_SSID "Hanya 10rb dapat Kuota Harian"
#define WIFI_PASSWORD "kentod69"

// Firebase project credentials
#define FIREBASE_HOST "https://emergency-help-422313-default-rtdb.asia-southeast1.firebasedatabase.app/"
#define FIREBASE_AUTH "4uG8yvfTGeTGb9Bbb7FAm7Ep34CikgdQ7MG4vkxM"

// Button pin
const int buttonPin = 14; // GPIO 14

// A9G module pins
const int rxPin = 22; // RX pin of the A9G module
const int txPin = 23; // TX pin of the A9G module

SoftwareSerial a9gSerial(rxPin, txPin); // Software serial for A9G module
TinyGPSPlus gps; // GPS object

FirebaseData firebaseData;
FirebaseAuth auth;
FirebaseConfig config;

String phoneNumber = ""; // Phone number to send the location, initialized empty
String emergencyMessage = ""; // Message to send in the SMS, initialized empty

void setup() {
  Serial.begin(9600);
  a9gSerial.begin(9600); // A9G module baud rate
  pinMode(buttonPin, INPUT_PULLUP); // Initialize button pin

  // Wait for the power to stabilize
  delay(5000); // Wait 5 seconds

  // Connect to Wi-Fi
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Connecting to Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println();
  Serial.print("Connected with IP: ");
  Serial.println(WiFi.localIP());

  // Configure Firebase
  config.database_url = FIREBASE_HOST;
  config.signer.tokens.legacy_token = FIREBASE_AUTH;

  // Initialize Firebase
  Firebase.begin(&config, &auth);
  Firebase.reconnectWiFi(true);

  // Initialize A9G module
  initA9G();

  // Retrieve phone number and message from Firebase
  getPhoneNumberFromFirebase();
  getMessageFromFirebase();
}

void loop() {
  // Feed GPS data to the TinyGPS++ library
  while (a9gSerial.available() > 0) {
    gps.encode(a9gSerial.read());
  }

  // Check if the button is pressed
  if (digitalRead(buttonPin) == LOW) {
    if (!phoneNumber.isEmpty() && !emergencyMessage.isEmpty()) {
      bool locationSentSuccessfully = sendLocationViaSMS(phoneNumber);
      if (locationSentSuccessfully) {
        delay(5000); // Wait for 5 seconds after sending the SMS
        makeCall(phoneNumber); // Make the call after SMS is sent
      } else {
        Serial.println("Failed to send location via SMS. Skipping call.");
      }
    } else {
      Serial.println("Phone number or message is empty, skipping SMS and call.");
    }
    delay(5000); // Debounce delay
  }
}

void getPhoneNumberFromFirebase() {
  // Path to the phone number in the Firebase Realtime Database
  String path = "/user_data/1/phone";

  // Get the phone number from Firebase
  if (Firebase.RTDB.getString(&firebaseData, path)) {
    phoneNumber = firebaseData.stringData();
    Serial.print("Retrieved phone number from Firebase: ");
    Serial.println(phoneNumber);
  } else {
    Serial.print("Failed to get phone number from Firebase: ");
    Serial.println(firebaseData.errorReason());
  }
}

void getMessageFromFirebase() {
  // Path to the emergency message in the Firebase Realtime Database
  String path = "/user_data/1/message";

  // Get the emergency message from Firebase
  if (Firebase.RTDB.getString(&firebaseData, path)) {
    emergencyMessage = firebaseData.stringData();
    Serial.print("Retrieved emergency message from Firebase: ");
    Serial.println(emergencyMessage);
  } else {
    Serial.print("Failed to get emergency message from Firebase: ");
    Serial.println(firebaseData.errorReason());
  }
}

bool sendLocationViaSMS(String phoneNumber) {
  if (gps.location.isUpdated()) {
    double lat = gps.location.lat();
    double lng = gps.location.lng();

    String latitude = String(lat, 6);
    String longitude = String(lng, 6);

    // Format the Google Maps link
    String googleMapsLink = "https://www.google.com/maps/place/" + latitude + "," + longitude;

    // Store data to Firebase
    FirebaseJson json;
    json.set("user", true);
    json.set("ID Hardware", 1);
    json.set("MapLocation", googleMapsLink);
    json.set("Number", phoneNumber);

    if (Firebase.RTDB.setJSON(&firebaseData, "/location", &json)) {
      Serial.println("Location data updated in Firebase");
    } else {
      Serial.println("Failed to update location data in Firebase");
      Serial.println(firebaseData.errorReason());
    }

    // Send the location via SMS
    String message = emergencyMessage + " Here is my current location: " + googleMapsLink;
    String smsCommand = "AT+CMGS=\"" + phoneNumber + "\"\r";
    a9gSerial.println(smsCommand);
    delay(1000);
    a9gSerial.println(message);
    delay(1000);
    sendCommand(String(char(26))); // Send control+Z to send the SMS
    delay(1000);
    Serial.println("Location shared via SMS");
    return true;
  } else {
    Serial.println("Failed to get location for SMS");
    return false;
  }
}

void makeCall(String phoneNumber) {
  String callCommand = "ATD" + phoneNumber + ";";
  a9gSerial.println(callCommand);

  bool callConnected = false;
  unsigned long startTime = millis();
  while (!callConnected && millis() - startTime < 60000) { // Wait for 60 seconds
    if (a9gSerial.available()) {
      String response = a9gSerial.readString();
      Serial.println(response);
      if (response.indexOf("CONNECT") != -1 || response.indexOf("OK") != -1) { // Call connected
        callConnected = true;
      }
    }
  }

  if (!callConnected) {
    Serial.println("Failed to connect the call");
  }

  delay(10000); // Wait for 10 seconds after the call
  a9gSerial.println("ATH"); // Hang up the call
}

String convertToDMS(double coord, bool isLatitude) {
  String direction;
  if (isLatitude) {
    direction = (coord >= 0) ? "N" : "S";
  } else {
    direction = (coord >= 0) ? "E" : "W";
  }
  
  coord = abs(coord);
  int degrees = int(coord);
  double minutes = (coord - degrees) * 60;
  int intMinutes = int(minutes);
  double seconds = (minutes - intMinutes) * 60;
  
  char buffer[20];
  sprintf(buffer, "%d°%d'%d\"%s", degrees, intMinutes, int(seconds), direction.c_str());
  return String(buffer);
}

void sendCommand(String command) {
  a9gSerial.println(command);
  delay(500);
  while (a9gSerial.available() > 0) {
    Serial.write(a9gSerial.read());
  }
}

void initA9G() {
  Serial.println("Initializing GPRS module...");
  delay(500); // Wait for the module to stabilize
  a9gSerial.println("AT"); // Check if the module is responding
  delay(500);

  sendCommand("AT+GPS=1\r");
  delay(500);

  sendCommand("AT+CREG=2\r");
  delay(5000);

  sendCommand("AT+CGATT=1\r");
  delay(5000);

  sendCommand("AT+CGDCONT=1,\"IP\",\"WWW\"\r");
  delay(5000);

  sendCommand("AT+CGACT=1,1\r");
  delay(5000);

  sendCommand("AT+GPS=1\r");
  delay(5000);

  sendCommand("AT+GPSRD=10\r");
  delay(500);

  // Configure GSM settings
  a9gSerial.println("AT+CSCS=\"GSM\""); // Set character set
  delay(500);

  a9gSerial.println("AT+CLIP=1"); // Enable Caller ID
  delay(500);

  // Configure SMS settings
  a9gSerial.println("AT+CMGF=1"); // Set SMS text mode
  delay(1000);
}