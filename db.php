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
        role ENUM('admin','user') DEFAULT 'user',
        time_login DATETIME,
        time_logout DATETIME
    )";
    $result=$conn->query($myquery); // Thực thi câu lệnh tạo bảng

    //Bảng vocabulary_Set
    $myquery="CREATE TABLE IF NOT EXISTS vocabulary_set (
        vocabularySet_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        vocabulary_name VARCHAR(200) NOT NULL UNIQUE,
        description TEXT,
        vocabulary_type ENUM ('personal', 'default') NOT NULL,
        FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
    )";
    $result=$conn->query($myquery);

    //Bảng Flashcard
    $myquery="CREATE TABLE IF NOT EXISTS flashcard (
        flashcard_id INT AUTO_INCREMENT PRIMARY KEY,
        vocabularySet_id INT,
        vocab VARCHAR(100) NOT NULL,
        image_path VARCHAR(255),
        ipa VARCHAR(100),
        meaning TEXT NOT NULL,
        flashcard_type VARCHAR(50),
        example TEXT,
        FOREIGN KEY (vocabularySet_id) REFERENCES vocabulary_set(vocabularySet_id) ON DELETE CASCADE
    )";
    $result=$conn->query($myquery);

    //Bảng game_history
    $myquery="CREATE TABLE IF NOT EXISTS history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    duration TIME,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
    )";
    $result=$conn->query($myquery);

    //Bảng Thông báo
    $myquery="CREATE TABLE IF NOT EXISTS reminder (
        reminder_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        reminder_date DATE NOT NULL,
        status ENUM ('Complete','Pending') DEFAULT 'Pending',
        FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
        )";
    $result=$conn->query($myquery);

    //bảng tin nhắn
    $myquery = "CREATE TABLE IF NOT EXISTS default_messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        message_type ENUM('daily', '48_hours') NOT NULL,
        message_text TEXT NOT NULL
    )";
    $result=$conn->query($myquery); 


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
        INSERT INTO flashcard (vocabularySet_id, vocab, image_path, ipa, meaning, flashcard_type, example)
        VALUES
        (1, 'Hello', 'https://i.pinimg.com/736x/ef/c0/32/efc032e6aab09b1cd085ceee81a43259.jpg', '/həˈloʊ/', 'Xin chào', 'noun', 'Hello Jenny, have a good day!'),
        (1, 'Goodbye', 'https://png.pngtree.com/png-clipart/20231005/original/pngtree-smiling-diverse-employees-wave-saying-goodbye-people-illustration-gesture-vector-png-image_12962087.png', '/ɡʊdˈbaɪ/', 'Tạm biệt', 'noun', 'Bye Tom, see you later!'),
        (1, 'Computer', 'https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcTRFbxhH3AuuXWzmWu9h03HSacgxXq5ztYYdKeSML9QgcpzJTc8', '/kəmˈpjuː.tər/', 'Máy tính', 'noun', 'A good computer'),
        (1, 'Link', 'https://cdn.pixabay.com/photo/2022/01/11/21/48/link-6931554_1280.png', '/lɪŋk/', 'đường dẫn', 'noun', 'I sent you a link to the article.'),
        (1, 'Password', 'https://media.wired.com/photos/641e1a1b43ffd37beea02cdf/1:1/w_4065,h_4065,c_limit/Best%20Password%20Managers%20Gear%20GettyImages-1408198405.png', '/ˈpæˌswɜrd/', 'mật khẩu', 'noun', 'Change your password regularly for security reasons.'),
        (1, 'Program', 'https://ooc.vn/wp-content/uploads/2023/10/Program-la-gi-2.png', '/ˈproʊˌgræm/', 'chương trình máy tính', 'noun', 'The program has many useful features.'),
        (1, 'Sign up', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSI8RuTWFe8rQ4zp_wubfJMI_R4mD7qPZl5sQ&s', '/saɪn ʌp/', 'đăng ký', 'noun', 'You need to sign up for an account to participate in the event.'),
        (1, 'Smartphone', 'https://cdn.tgdd.vn/Files/2023/08/06/1541395/smartphone-tgdd-33312313-2-060823-210136-800-resize.jpg', '/smärtˌfōn/', 'điện thoại thông minh', 'noun', 'She uses her smartphone for online shopping.'),
        (1, 'Software', 'https://www.coderus.com/wp-content/uploads/2020/11/different-types-of-software-coderus-branded-image.jpg', '/ˈsɔfˌtwɛr/', 'phần mềm', 'noun', 'You need to update your software for the latest features.'),
        (1, 'Speaker', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRhSNALxqI1jtNh7IZFYCRhrhtcM_qx2Fd5WQ&s', '/ˈspikər/', 'loa', 'noun', 'The speaker produces excellent sound quality.'),
        (1, 'Surf', 'https://hoc247.net/fckeditorimg/upload/images/trac-nghiem-family-and-friends-5-unit-2-lesson-3-exercise-3.png', '/sɜrf/', 'lướt (web)', 'verb', 'I love to surf the internet in my free time.'),
        (1, 'System', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRIrj-LQiWob905N9cMrOKQNk7cBweNrFlYrg&s', '/ˈsɪstəm/', 'hệ thống', 'noun', 'The computer system crashed due to a technical issue.'),
        (1, 'Call', 'https://hidosport.vn/wp-content/uploads/2023/09/call-icon.png', '/kɔl/', 'gọi điện thoại', 'verb', 'I need to call my friend to confirm the details.'),
        (1, 'Cellphone', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm5hHANz6aT-5NcugwT6goIfCECJFzLUW4pQ&s', '/ˈsɛlfoʊn/', 'điện thoại di động', 'noun', 'She uses her cellphone to stay in touch with her family.'),
        (1, 'Communicate', 'https://cdn.thomasgriffin.com/wp-content/uploads/2021/02/how-to-communicate-effectively-hero-social.png', '/kəmˈjunəˌkeɪt/', 'giao tiếp', 'verb', 'They communicate through emails and phone calls.'),
        (1, 'Contact', 'https://accgroup.vn/wp-content/uploads/2023/02/contact-us-1908762__340.webp', '/ˈkɑnˌtækt/', 'liên hệ; (n) địa chỉ liên hệ', 'verb', 'You can contact me via email or phone.'),
        (1, 'Hotline', 'https://worldfone.vn/pictures/getfile/Dau-so-hotline-la-gi.jpg', '/ˈhɑtˌlaɪn/', 'đường dây nóng', 'noun', 'The company offers a 24/7 hotline for customer support.'),
        (1, 'Message', 'https://play-lh.googleusercontent.com/c5HiVEILwq4DqYILPwcDUhRCxId_R53HqV_6rwgJPC0j44IaVlvwASCi23vGQh5G3LIZ', '/ˈmɛsəʤ/', 'tin nhắn', 'noun', 'I received a message from my colleague this morning.'),
        (1, 'Missed', 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcStryT0dDuL34pFd9flEH55z0uVqxBTW4wldnJ55G_-sKfTtirX', '/mɪst/', 'lỡ, nhỡ', 'verb', 'I missed your call, can you call me back?'),
        (1, 'Phone number', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRM7aHNjqi1-8dgUERookAqJvgRu1xlYRktIA&s', '/foʊn ˈnʌmbər/', 'số điện thoại', 'noun', 'Please leave your phone number so I can contact you later.'),
        (1, 'Receive', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSA1v0z7QJg9asZ9UET4hGMuLpaEzMnmVybbA&s', '/rəˈsiv/', 'nhận được', 'verb', 'Did you receive my email yesterday?'),
        (1, 'Send', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT6aNkf7gBzBn70l3b3_NPOD-xC5mHllY0ZmQ&s', '/sɛnd/', 'gửi đi', 'verb', 'I will send the documents via email.'),
        (1, 'Signature', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQuWbVAIQPLFOSrX3Iog11KdDP_UfMhmOpl5Q&s', '/ˈsɪgnəʧər/', 'chữ ký', 'noun', 'You need to sign your name at the bottom of the document.'),
        (1, 'Stamp', 'https://assets.manufactum.de/p/040/040904/40904_01.jpg/hand-stamp-beech-wood.jpg', '/stæmp/', 'tem', 'noun', 'I need to buy a stamp to send the letter.'),
        (1, 'Text', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTa1wneG_mCPJdd_PM4YNwFBUPcuqdRyn0ULw&s', '/tɛkst/', 'nhắn tin; tin nhắn (n)', 'verb', 'I will text you the address shortly.')
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

    // Thêm dữ liệu mẫu vào bảng reminder
    $insert_reminder_query = "
        INSERT INTO reminder (user_id, reminder_date, status)
        VALUES 
        (1, '2024-11-30', 'Pending'),
        (2, '2024-12-01', 'Complete')
    ";
    //$conn->query($insert_reminder_query);

    $insert_default_messages_query = "INSERT INTO default_messages (message_type, message_text) VALUES
    ('daily', 'Hôm nay Apple có nhiều từ vựng mới thú vị lắm nè, bạn {username} vào học cùng tớ nhé =33 ♥♥♥.'),
    ('48_hours', 'Đằng ấy ơi, bạn có niềm vui gì mới mà quên luôn Apple rồi sao? Apple cảm thấy buồn, hãy vô học với Apple để Apple thấy vui hơn nhé ^^.')";
    //$conn->query($insert_default_messages_query);


    
    ?>