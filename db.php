<?php
    //connect
    $config_severname="localhost";
    $config_name="root";
    $config_password="";
    $config_database="e-learn-app";
    $conn=new mysqli($config_severname,$config_name,$config_password,$config_database) or die('connection failed');
    
    //Bảng User
    $myquery="CREATE TABLE IF NOT EXISTS user (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(200) NOT NULL,
        email VARCHAR(200) NOT NULL UNIQUE,
        phoneNumber VARCHAR(10),
        password VARCHAR(255) NOT NULL,
        dateSignin DATETIME DEFAULT CURRENT_TIMESTAMP,
        role ENUM('admin','user') DEFAULT 'user'
    )";
    //$result=$conn->query($myquery); // Thực thi câu lệnh tạo bảng
    //Bảng vocabulary_Set
    $myquery="CREATE TABLE IF NOT EXISTS vocabulary_set (
        vocabularySet_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        vocabulary_name VARCHAR(200) NOT NULL UNIQUE,
        description TEXT,
        vocabulary_type ENUM ('personal', 'default') NOT NULL,
        FOREIGN KEY (user_id) REFERENCES user(user_id)
    )";
    //$result=$conn->query($myquery);

    //Bảng Flashcard
    $myquery="CREATE TABLE IF NOT EXISTS flashcard (
        flashcard_id INT AUTO_INCREMENT PRIMARY KEY,
        vocabularySet_id INT,
        vocab VARCHAR(100) NOT NULL,
        image_path VARCHAR(255),
        ipa VARCHAR(100),
        meaning TEXT NOT NULL,
        FOREIGN KEY (vocabularySet_id) REFERENCES vocabulary_set(vocabularySet_id) ON DELETE CASCADE
    )";
    //$result=$conn->query($myquery);

    //Bảng game_history
    $myquery="CREATE TABLE IF NOT EXISTS history (
    History_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    duration TIME,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
    )";
    //$result=$conn->query($myquery);

    // Bảng Game
    $myquery="CREATE TABLE IF NOT EXISTS game (
        game_id INT PRIMARY KEY AUTO_INCREMENT,
        game_name VARCHAR(50) NOT NULL,
        description TEXT,
        user_id INT NOT NULL,
        score INT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES user(user_id)
        )";
    //$result=$conn->query($myquery);

    //Bảng Thông báo
    $myquery="CREATE TABLE IF NOT EXISTS reminder (
        reminder_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        reminder_date DATE NOT NULL,
        status ENUM ('Complete','Pending') DEFAULT 'Pending',
        FOREIGN KEY (user_id) REFERENCES user(user_id)
        )";
    //$result=$conn->query($myquery);

    //bảng tin nhắn
    $myquery = "CREATE TABLE IF NOT EXISTS default_messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        message_type ENUM('daily', '48_hours') NOT NULL,
        message_text TEXT NOT NULL
    )";
    $result=$conn->query($myquery); // Thực thi câu lệnh tạo bảng

    $insert_default_messages_query = "INSERT INTO default_messages (message_type, message_text) VALUES
    ('daily', 'Hôm nay Apple có nhiều từ vựng mới thú vị lắm nè, bạn {username} vào học cùng tớ nhé =33 ♥♥♥.'),
    ('48_hours', 'Đằng ấy ơi, bạn có niềm vui gì mới mà quên luôn Apple rồi sao? Apple cảm thấy buồn, hãy vô học với Apple để Apple thấy vui hơn nhé ^^.')";
    //$conn->query($insert_default_messages_query);  // Thực thi câu lệnh chèn dữ liệu


    //insert
    $insert_user_query = "
    INSERT INTO user (username, email, phoneNumber, password, role)
    VALUES 
    ('admin', 'admin@gmail.com', '0123456789', '" . password_hash("123", PASSWORD_DEFAULT) . "', 'admin'),
    ('user1', 'user@gmail.com', '0987654321', '" . password_hash("123", PASSWORD_DEFAULT) . "', 'user')
    ";
    //$conn->query($insert_user_query);


    // Thêm dữ liệu mẫu vào bảng vocabulary_set
    $insert_vocabulary_set_query = "
        INSERT INTO vocabulary_set (user_id, vocabulary_name, description, vocabulary_type)
        VALUES 
        (1, 'Common Words', 'A set of common English words.', 'default'),
        (2, 'Personal List', 'User-defined vocabulary list.', 'personal')
    ";
    //$conn->query($insert_vocabulary_set_query);

    // Thêm dữ liệu mẫu vào bảng flashcard
    $insert_flashcard_query = "
        INSERT INTO flashcard (vocabularySet_id, vocab,image_path, ipa, meaning)
        VALUES 
        (1, 'Hello', 'hello.png', '/həˈloʊ/', 'Xin chào'),
        (1, 'Goodbye', 'goodbye.png', '/ɡʊdˈbaɪ/', 'Tạm biệt'),
        (2, 'Computer', 'computer.png', '/kəmˈpjuː.tər/', 'Máy tính')
    ";
    //$conn->query($insert_flashcard_query);


    // Thêm dữ liệu mẫu vào bảng history
    $insert_history_query = "
        INSERT INTO history (user_id, duration)
        VALUES 
        (1, '00:30:00'),
        (2, '01:00:00')
    ";
    //$conn->query($insert_history_query);


    // Thêm dữ liệu mẫu vào bảng game
    $insert_game_query = "
        INSERT INTO game (game_name, description, user_id, score)
        VALUES 
        ('Vocabulary Quiz', 'Test your vocabulary knowledge.', 1, 100),
        ('Memory Game', 'Match words with their meanings.', 2, 80)
    ";
    //$conn->query($insert_game_query);

    // Thêm dữ liệu mẫu vào bảng reminder
    $insert_reminder_query = "
        INSERT INTO reminder (user_id, reminder_date, status)
        VALUES 
        (1, '2024-11-30', 'Pending'),
        (2, '2024-12-01', 'Complete')
    ";
    //$conn->query($insert_reminder_query);
    
    ?>