<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The 27 street names + plaque bios from Daniel's "City and Map" doc
 * (2026-10-19), word-for-word. Tiers match the doc's own grouping
 * (TOP TIER .. FIFTH TIER, plus a "marxist" tier for the two additions).
 */
return new class extends Migration
{
    public function up()
    {
        $streets = [
            ['tier' => '1', 'name' => 'Adam Smith Avenue', 'bio' => "Father of modern economics (1723-1790). Wrote 'The Wealth of Nations' explaining how free markets and the 'invisible hand' guide economies. His ideas form the foundation of capitalism."],
            ['tier' => '1', 'name' => 'Buffett Boulevard', 'bio' => "Warren Buffett (1930-present) - 'Oracle of Omaha.' Billionaire investor who teaches that patience, compound interest, and buying good companies cheap creates lasting wealth."],
            ['tier' => '1', 'name' => 'Keynes Boulevard', 'bio' => 'John Maynard Keynes (1883-1946) - British economist who showed how government spending can end recessions. Changed how we think about economic crises and recovery.'],
            ['tier' => '1', 'name' => 'Marx Street', 'bio' => 'Karl Marx (1818-1883) - German philosopher who critiqued capitalism, predicting class struggle between workers and owners. His ideas inspired communist movements worldwide.'],
            ['tier' => '1', 'name' => 'Vitoria Street', 'bio' => "Francisco de Vitoria (1483-1546) - Spanish theologian from University of Salamanca. Pioneer of 'just price' theory and international trade ethics. Founded economic morality studies."],

            ['tier' => '2', 'name' => 'Friedman Drive', 'bio' => 'Milton Friedman (1912-2006) - Nobel Prize winner who championed free markets and explained how money supply affects inflation. Advisor to presidents and central banks.'],
            ['tier' => '2', 'name' => 'Marshall Circle', 'bio' => 'Alfred Marshall (1842-1924) - British economist who created supply and demand curves. His graphs help us understand how prices form in markets.'],
            ['tier' => '2', 'name' => 'Say Boulevard', 'bio' => "Jean-Baptiste Say (1767-1832) - French economist famous for 'Say's Law': supply creates its own demand. Showed how production drives economic growth."],
            ['tier' => '2', 'name' => 'Hayek Highway', 'bio' => 'Friedrich Hayek (1899-1992) - Austrian economist and Nobel winner. Proved free markets process information better than government planning. Defended individual freedom.'],
            ['tier' => '2', 'name' => 'Molina Lane', 'bio' => 'Luis de Molina (1535-1600) - Spanish Jesuit from University of Salamanca. Developed early banking theory and explained just interest rates in lending.'],

            ['tier' => '3', 'name' => 'Graham Lane', 'bio' => "Benjamin Graham (1894-1976) - 'Father of value investing.' Taught Warren Buffett how to analyze companies and buy stocks below their true worth."],
            ['tier' => '3', 'name' => 'Fisher Street', 'bio' => 'Irving Fisher (1867-1947) - American economist who explained the time value of money and how interest rates work. Pioneer of mathematical economics.'],
            ['tier' => '3', 'name' => 'Walras Way', 'bio' => 'Léon Walras (1834-1910) - French economist who created general equilibrium theory, showing how all markets connect. Used mathematics to explain economics.'],
            ['tier' => '3', 'name' => 'Schumpeter Street', 'bio' => "Joseph Schumpeter (1883-1950) - Austrian economist who coined 'creative destruction' - how innovation destroys old industries while creating new ones."],
            ['tier' => '3', 'name' => 'Pareto Path', 'bio' => 'Vilfredo Pareto (1848-1923) - Italian economist who discovered the 80/20 rule and Pareto efficiency. Studied how wealth distributes in society.'],

            ['tier' => '4', 'name' => 'Samuelson Square', 'bio' => 'Paul Samuelson (1915-2009) - First American to win economics Nobel Prize. Wrote the most influential economics textbook, teaching millions of students.'],
            ['tier' => '4', 'name' => 'Lynch Avenue', 'bio' => "Peter Lynch (1944-present) - Legendary fund manager who proved ordinary investors can beat Wall Street by 'investing in what you know.'"],
            ['tier' => '4', 'name' => 'Bogle Boulevard', 'bio' => 'John Bogle (1929-2019) - Founded Vanguard and created index funds, revolutionizing low-cost investing. Made Wall Street accessible to ordinary people.'],
            ['tier' => '4', 'name' => 'Piketty Path', 'bio' => "Thomas Piketty (1971-present) - French economist who studied wealth inequality. His book 'Capital in the 21st Century' sparked global debates about fairness."],
            ['tier' => '4', 'name' => 'Mises Memorial Drive', 'bio' => 'Ludwig von Mises (1881-1973) - Austrian economist who defended free markets and showed why socialist economic calculation fails. Influenced libertarian thought.'],

            ['tier' => '5', 'name' => 'Kahneman Court', 'bio' => 'Daniel Kahneman (1934-2024) - Nobel Prize-winning psychologist who revealed how emotions and biases affect financial decisions. Founded behavioral economics.'],
            ['tier' => '5', 'name' => 'Veblen Road', 'bio' => "Thorstein Veblen (1857-1929) - American economist who coined 'conspicuous consumption' - buying expensive things to show status rather than utility."],
            ['tier' => '5', 'name' => 'Galbraith Grove', 'bio' => 'John Kenneth Galbraith (1908-2006) - Canadian-American economist who critiqued consumer culture and explained how advertising creates artificial demand.'],
            ['tier' => '5', 'name' => 'Sowell Street', 'bio' => 'Thomas Sowell (1930-present) - American economist who makes complex economic ideas simple and accessible. Prolific author on economics and social policy.'],
            ['tier' => '5', 'name' => 'Templeton Terrace', 'bio' => "John Templeton (1912-2008) - Pioneer of global investing who taught 'buy when others are selling.' Founded one of the world's largest mutual fund companies."],

            ['tier' => 'marxist', 'name' => 'Engels Avenue', 'bio' => "Friedrich Engels (1820-1895) - German philosopher and businessman who co-authored 'The Communist Manifesto' with Marx. Funded Marx's work and developed dialectical materialism."],
            ['tier' => 'marxist', 'name' => 'Gramsci Street', 'bio' => "Antonio Gramsci (1891-1937) - Italian Marxist who explained 'cultural hegemony' - how dominant classes control society through culture and ideas, not just economics."],
        ];

        foreach ($streets as $i => $street) {
            if (!DB::table('streets')->where('name', $street['name'])->exists()) {
                DB::table('streets')->insert($street + [
                    'sort_order' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('streets')->truncate();
    }
};
