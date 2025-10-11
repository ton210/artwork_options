package com.example.seinfeldtrivia;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class QuestionDatabase {
    private static List<Question> allQuestions;

    static {
        allQuestions = new ArrayList<>();
        loadQuestions();
    }

    private static void loadQuestions() {
        // EASY MODE QUESTIONS
        allQuestions.add(new Question(
            "What is Jerry's last name?",
            new String[]{"Seinfeld", "Costanza", "Kramer", "Benes"},
            0, "EASY"
        ));

        allQuestions.add(new Question(
            "What is the name of Jerry's neighbor across the hall?",
            new String[]{"George", "Elaine", "Kramer", "Newman"},
            2, "EASY"
        ));

        allQuestions.add(new Question(
            "What does Jerry do for a living?",
            new String[]{"Writer", "Comedian", "Doctor", "Architect"},
            1, "EASY"
        ));

        allQuestions.add(new Question(
            "What is George's last name?",
            new String[]{"Kramer", "Costanza", "Newman", "Ross"},
            1, "EASY"
        ));

        allQuestions.add(new Question(
            "What is Elaine's last name?",
            new String[]{"Benes", "Puddy", "Ross", "Peterman"},
            0, "EASY"
        ));

        allQuestions.add(new Question(
            "What is the name of the diner where the gang often meets?",
            new String[]{"Central Perk", "Monk's Cafe", "The Coffee Shop", "Tom's Restaurant"},
            1, "EASY"
        ));

        allQuestions.add(new Question(
            "What is Newman's job?",
            new String[]{"Mailman", "Police Officer", "Accountant", "Chef"},
            0, "EASY"
        ));

        allQuestions.add(new Question(
            "What city does Seinfeld take place in?",
            new String[]{"Los Angeles", "Chicago", "New York", "Boston"},
            2, "EASY"
        ));

        // MEDIUM MODE QUESTIONS
        allQuestions.add(new Question(
            "What is the name of George's boss at the New York Yankees?",
            new String[]{"Mr. Wilhelm", "Mr. Steinbrenner", "Mr. Ross", "Mr. Kruger"},
            1, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What does Kramer's first name start with?",
            new String[]{"K", "C", "G", "M"},
            0, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What is the name of Elaine's on-and-off boyfriend who is known for his deep voice?",
            new String[]{"David Puddy", "Jerry Seinfeld", "Kenny Rogers", "J. Peterman"},
            0, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What food item does Kramer slide across Jerry's counter?",
            new String[]{"Bagel", "Junior Mint", "Big Salad", "Mackinaw Peaches"},
            3, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What is the name of the soup restaurant where the 'Soup Nazi' works?",
            new String[]{"Soup Kitchen", "The Original Soup Man", "Soup Plus", "The Soup Stand"},
            1, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What does George claim to be an architect of?",
            new String[]{"The Statue of Liberty", "The Guggenheim", "Lincoln Center", "The addition to the Guggenheim"},
            3, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What is the name of Jerry's nemesis mailman?",
            new String[]{"Newman", "Newbert", "Neuman", "Newton"},
            0, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What magazine does Elaine work for?",
            new String[]{"Pendant Publishing", "J. Peterman Catalog", "Vanity Fair", "The New Yorker"},
            0, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What does Kramer do with his salad?",
            new String[]{"Throws it away", "Makes it bigger", "Eats it", "Gives it to Newman"},
            1, "MEDIUM"
        ));

        allQuestions.add(new Question(
            "What is George's middle name?",
            new String[]{"Louis", "Francis", "Michael", "Anthony"},
            0, "MEDIUM"
        ));

        // HARD MODE QUESTIONS
        allQuestions.add(new Question(
            "What is the name of the low-fat frozen yogurt shop?",
            new String[]{"I Can't Believe It's Yogurt", "TCBY", "White's Yogurt", "Columbo Yogurt"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What is the name of George's father?",
            new String[]{"Frank Costanza", "Morty Costanza", "Lou Costanza", "Sam Costanza"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What holiday does Frank Costanza create?",
            new String[]{"Festivus", "Frankmas", "Costanza Day", "Winter Solstice"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What is Kramer's first name?",
            new String[]{"Cosmo", "Calvin", "Chester", "Clarence"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What does J. Peterman call Elaine?",
            new String[]{"Elaine", "Miss Benes", "My right hand", "Urban Sombrero"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What is the name of the Japanese businessman who wants to buy Kramer's stories?",
            new String[]{"Mr. Yamaguchi", "Mr. Tanaka", "Mr. Yamamoto", "Mr. Watanabe"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What does George do when he thinks his boss is trying to fire him?",
            new String[]{"Shows up anyway", "Quits", "Files a complaint", "Goes on vacation"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What is the name of Jerry's father?",
            new String[]{"Morty Seinfeld", "Martin Seinfeld", "Morris Seinfeld", "Max Seinfeld"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What does Newman call Kramer?",
            new String[]{"Kramer", "K-man", "Cosmo", "My friend"},
            0, "HARD"
        ));

        allQuestions.add(new Question(
            "What is the name of Elaine's father?",
            new String[]{"Alton Benes", "Arthur Benes", "Albert Benes", "Andrew Benes"},
            0, "HARD"
        ));

        // EXPERT MODE QUESTIONS
        allQuestions.add(new Question(
            "What is the name of the restaurant where Jerry gets food poisoning?",
            new String[]{"Kenny Roger's Roasters", "Mendy's", "The Big Salad", "Reggie's"},
            1, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is the name of George's alias when he pretends to be a tourist?",
            new String[]{"Art Vandelay", "Buck Naked", "H.E. Pennypacker", "Dr. Martin van Nostrand"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is Kramer's mother's name?",
            new String[]{"Babs Kramer", "Betty Kramer", "Barbara Kramer", "Bonnie Kramer"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What does George's mother call him when she's angry?",
            new String[]{"Georgie", "You little weasel", "George Louis", "Serenity now"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is the name of the woman Jerry dates who has 'man hands'?",
            new String[]{"Gillian", "Gail", "Grace", "Gloria"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What does Frank Costanza sell during his brief business venture?",
            new String[]{"Computers", "Bras for men (The Bro)", "Cars", "Insurance"},
            1, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is the name of Kramer's friend who owns the vintage clothing store?",
            new String[]{"Morty", "Mickey", "Bob Sacamano", "Jay Riemenschneider"},
            2, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What does Elaine say when she doesn't want to have a baby?",
            new String[]{"Maybe the dingo ate your baby", "I don't want a baby", "The sponge", "I'm not ready"},
            2, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is the name of the street where Jerry lives?",
            new String[]{"West 81st Street", "West 83rd Street", "West 85th Street", "West 87th Street"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What does George do to get revenge on his boss?",
            new String[]{"Puts Mickey Finns in his drink", "Slips him a mickey", "Drugs his food", "Puts chloral hydrate in his drink"},
            1, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What is Puddy's favorite thing to say?",
            new String[]{"Yeah, that's right", "High five!", "Giddy up", "That's gold, Jerry!"},
            0, "EXPERT"
        ));

        allQuestions.add(new Question(
            "What does Kramer name his chicken?",
            new String[]{"Little Jerry", "Big Jerry", "Kramer Jr.", "Newman Jr."},
            0, "EXPERT"
        ));
    }

    public static List<Question> getQuestionsByDifficulty(String difficulty) {
        List<Question> filteredQuestions = new ArrayList<>();
        for (Question question : allQuestions) {
            if (question.getDifficulty().equals(difficulty)) {
                filteredQuestions.add(question);
            }
        }
        Collections.shuffle(filteredQuestions);
        return filteredQuestions.subList(0, Math.min(10, filteredQuestions.size()));
    }
}