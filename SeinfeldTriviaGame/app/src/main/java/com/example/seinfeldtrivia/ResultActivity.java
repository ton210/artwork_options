package com.example.seinfeldtrivia;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

public class ResultActivity extends AppCompatActivity {
    private int score;
    private int total;
    private String difficulty;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_result);

        // Get results from intent
        score = getIntent().getIntExtra("SCORE", 0);
        total = getIntent().getIntExtra("TOTAL", 10);
        difficulty = getIntent().getStringExtra("DIFFICULTY");

        // Initialize views
        TextView tvDifficultyResult = findViewById(R.id.tv_difficulty_result);
        TextView tvFinalScore = findViewById(R.id.tv_final_score);
        TextView tvPerformanceMessage = findViewById(R.id.tv_performance_message);
        Button btnPlayAgain = findViewById(R.id.btn_play_again);
        Button btnMainMenu = findViewById(R.id.btn_main_menu);

        // Display results
        tvDifficultyResult.setText(difficulty + " MODE");
        tvFinalScore.setText("Your Score: " + score + "/" + total);
        tvPerformanceMessage.setText(getPerformanceMessage());

        // Set up button listeners
        btnPlayAgain.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                playAgain();
            }
        });

        btnMainMenu.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                goToMainMenu();
            }
        });
    }

    private String getPerformanceMessage() {
        double percentage = (double) score / total * 100;
        
        if (percentage == 100) {
            return "Master of Your Domain!\nPerfect Score!";
        } else if (percentage >= 90) {
            return "Master of Your Domain!\nYou're gold, Jerry! Gold!";
        } else if (percentage >= 80) {
            return "Serenity Now!\nExcellent knowledge!";
        } else if (percentage >= 70) {
            return "That's Gold, Jerry!\nPretty, pretty good!";
        } else if (percentage >= 60) {
            return "Yada Yada Yada...\nNot bad at all!";
        } else if (percentage >= 50) {
            return "Newman!\nYou can do better!";
        } else {
            return "No Soup For You!\nBetter luck next time!";
        }
    }

    private void playAgain() {
        Intent intent = new Intent(this, GameActivity.class);
        intent.putExtra("DIFFICULTY", difficulty);
        startActivity(intent);
        finish();
    }

    private void goToMainMenu() {
        Intent intent = new Intent(this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        startActivity(intent);
        finish();
    }
}