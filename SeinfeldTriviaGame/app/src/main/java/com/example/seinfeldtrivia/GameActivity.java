package com.example.seinfeldtrivia;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import java.util.List;

public class GameActivity extends AppCompatActivity {
    private List<Question> questions;
    private int currentQuestionIndex = 0;
    private int score = 0;
    private String difficulty;
    private boolean answerSelected = false;
    
    private TextView tvProgress;
    private TextView tvScore;
    private TextView tvDifficulty;
    private TextView tvQuestion;
    private Button btnAnswer1, btnAnswer2, btnAnswer3, btnAnswer4;
    private Button btnNext;
    private Button[] answerButtons;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_game);

        // Get difficulty from intent
        difficulty = getIntent().getStringExtra("DIFFICULTY");
        
        // Initialize views
        initializeViews();
        
        // Load questions for the selected difficulty
        questions = QuestionDatabase.getQuestionsByDifficulty(difficulty);
        
        // Display first question
        displayQuestion();
    }

    private void initializeViews() {
        tvProgress = findViewById(R.id.tv_progress);
        tvScore = findViewById(R.id.tv_score);
        tvDifficulty = findViewById(R.id.tv_difficulty);
        tvQuestion = findViewById(R.id.tv_question);
        btnAnswer1 = findViewById(R.id.btn_answer1);
        btnAnswer2 = findViewById(R.id.btn_answer2);
        btnAnswer3 = findViewById(R.id.btn_answer3);
        btnAnswer4 = findViewById(R.id.btn_answer4);
        btnNext = findViewById(R.id.btn_next);
        
        answerButtons = new Button[]{btnAnswer1, btnAnswer2, btnAnswer3, btnAnswer4};
        
        // Set difficulty text
        tvDifficulty.setText(difficulty + " MODE");
        
        // Set up answer button listeners
        for (int i = 0; i < answerButtons.length; i++) {
            final int answerIndex = i;
            answerButtons[i].setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    if (!answerSelected) {
                        selectAnswer(answerIndex);
                    }
                }
            });
        }
        
        // Set up next button listener
        btnNext.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                nextQuestion();
            }
        });
    }

    private void displayQuestion() {
        if (currentQuestionIndex < questions.size()) {
            Question currentQuestion = questions.get(currentQuestionIndex);
            
            // Update progress
            tvProgress.setText("Question " + (currentQuestionIndex + 1) + " of " + questions.size());
            
            // Update score
            tvScore.setText("Score: " + score);
            
            // Display question
            tvQuestion.setText(currentQuestion.getQuestion());
            
            // Display answer options
            String[] options = currentQuestion.getOptions();
            for (int i = 0; i < answerButtons.length; i++) {
                answerButtons[i].setText(options[i]);
                answerButtons[i].setBackgroundColor(Color.parseColor("#E3F2FD"));
                answerButtons[i].setEnabled(true);
            }
            
            // Hide next button and reset answer selection
            btnNext.setVisibility(View.GONE);
            answerSelected = false;
        } else {
            // Game finished - go to results
            finishGame();
        }
    }

    private void selectAnswer(int selectedIndex) {
        answerSelected = true;
        Question currentQuestion = questions.get(currentQuestionIndex);
        
        // Disable all buttons
        for (Button button : answerButtons) {
            button.setEnabled(false);
        }
        
        // Show correct answer in green
        answerButtons[currentQuestion.getCorrectAnswerIndex()].setBackgroundColor(Color.parseColor("#4CAF50"));
        
        if (currentQuestion.isCorrect(selectedIndex)) {
            // Correct answer
            score++;
            if (selectedIndex != currentQuestion.getCorrectAnswerIndex()) {
                // This shouldn't happen, but just in case
                answerButtons[selectedIndex].setBackgroundColor(Color.parseColor("#4CAF50"));
            }
        } else {
            // Wrong answer - show selected answer in red
            answerButtons[selectedIndex].setBackgroundColor(Color.parseColor("#F44336"));
        }
        
        // Update score display
        tvScore.setText("Score: " + score);
        
        // Show next button after a short delay
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                btnNext.setVisibility(View.VISIBLE);
            }
        }, 1000);
    }

    private void nextQuestion() {
        currentQuestionIndex++;
        displayQuestion();
    }

    private void finishGame() {
        Intent intent = new Intent(this, ResultActivity.class);
        intent.putExtra("SCORE", score);
        intent.putExtra("TOTAL", questions.size());
        intent.putExtra("DIFFICULTY", difficulty);
        startActivity(intent);
        finish();
    }

    @Override
    public void onBackPressed() {
        // Prevent accidental back navigation during game
        // Could add a confirmation dialog here
        super.onBackPressed();
    }
}