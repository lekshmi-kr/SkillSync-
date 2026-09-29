# SkillSync

SkillSync is a web-based platform designed to connect students with tutors for extracurricular skills such as music, dance, art, coding, sports, and more.

The platform helps students discover tutors based on their learning preferences and the tutor's teaching approach. It also provides features for session booking, messaging, learning progress tracking, attendance, ratings, trial feedback, and tutor credibility.

## ✨ Features

### For Students
- Create and manage a student account
- Browse and search for tutors
- View tutor profiles and skills
- Set preferred learning style and learning pace
- View compatibility scores with tutors
- Book demo and learning sessions
- Communicate with tutors through messaging
- Track learning progress
- Submit trial feedback
- Rate tutors
- View attendance information

### For Tutors
- Register and create a tutor profile
- Add skills and experience information
- Upload certificates during registration
- Set teaching style and teaching pace
- Manage student bookings
- Communicate with students
- Record attendance
- Track trial outcomes
- View credibility information

## 🎯 Student–Tutor Compatibility

SkillSync includes a compatibility system designed to help students find tutors whose teaching approach matches their learning preferences.

The compatibility calculation considers:

- Learning Style vs Teaching Style
- Learning Pace vs Teaching Pace

A compatibility score between **0 and 100** is generated to indicate how closely the student's preferences match the tutor's teaching approach.

## ⭐ Tutor Credibility

The platform also provides a tutor credibility system based on factors such as:

- Student ratings
- Certificate availability
- Session/demo completion

This provides students with additional information when evaluating tutors.

## 💬 Trial Feedback and Recommendations

After a trial session, students and tutors can provide feedback.

The platform can use the trial outcome to provide recommendations such as continuing with the tutor, adjusting the learning level, or considering another tutor.

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| PHP | Server-side logic |
| MySQL | Database management |
| HTML | Page structure |
| CSS | Styling and user interface |
| JavaScript | Client-side interactions |
| MySQLi | PHP and MySQL communication |

## 📁 Project Structure

```text
SkillSync/
├── css/
├── images/
├── includes/
│   ├── compatibility.php
│   ├── credibility.php
│   ├── navbar.php
│   └── recommendation.php
├── js/
├── pages/
├── connect.php
├── full_setup.sql
├── index.php
├── login.php
├── logout.php
├── register.php
└── register_student.php
```

## 🚀 Getting Started

### Prerequisites

To run SkillSync locally, you will need:

- PHP
- MySQL
- A local web server such as XAMPP, WAMP, or MAMP
- A modern web browser

### Installation

1. Clone the repository:

```bash
git clone https://github.com/lekshmi-kr/SkillSync-.git
```

2. Move the project into your local web server directory.

For example, when using XAMPP:

```text
xampp/htdocs/
```

3. Start **Apache** and **MySQL**.

4. Open phpMyAdmin or another MySQL management tool.

5. Create the required database and import:

```text
full_setup.sql
```

6. Configure your local database connection in `connect.php`.

7. Open the project through your local server. For example:

```text
http://localhost/SkillSync-/
```

The exact URL may vary depending on your local project directory.

## 🔐 Security

Database credentials and other sensitive information should not be committed to a public repository. For production deployments, sensitive configuration should be stored securely using environment variables or another appropriate configuration method.

## 🔮 Future Improvements

- Improved tutor recommendation and matching
- Advanced tutor search and filtering
- Real-time chat
- Notifications
- Enhanced tutor verification
- Improved responsive design
- Online session integration
- Learning analytics and progress visualization

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository.
2. Create a branch for your changes.
3. Make and test your changes.
4. Commit your changes with a clear commit message.
5. Push the branch to your fork.
6. Open a pull request.

## 📄 License

No license has currently been specified for this project.
