package com.example.seinfeldtrivia;

public class Question {
    private String question;
    private String[] options;
    private int correctAnswerIndex;
    private String difficulty;

    public Question(String question, String[] options, int correctAnswerIndex, String difficulty) {
        this.question = question;
        this.options = options;
        this.correctAnswerIndex = correctAnswerIndex;
        this.difficulty = difficulty;
    }

    public String getQuestion() {
        return question;
    }

    public String[] getOptions() {
        return options;
    }

    public int getCorrectAnswerIndex() {
        return correctAnswerIndex;
    }

    public String getDifficulty() {
        return difficulty;
    }

    public boolean isCorrect(int selectedIndex) {
        return selectedIndex == correctAnswerIndex;
    }
}