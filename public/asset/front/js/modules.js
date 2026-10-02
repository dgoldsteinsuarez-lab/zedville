// Financial Literacy Module Data (Pure JavaScript)

const modules = [
  {
    id: 1,
    activity_key: 'task_1',
    title: "Introduction to Budget",
    icon: "🐷",
    color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What's a Budget?",
        text: [
          "A budget is like a simple guide for your money. It helps you:",
          "• Know your money - How much money is coming in",
          "• See where it goes - What you spend your money on",
          "• Plan to save - Set aside some money for things you want in the future"
          
        ],
        subtitle: "Why make choices about our money?",
        subtext:
          "The money we have now is limited. Whether it's your allowance, gifts, or even money you might earn later, there's only so much of it. This means we need to make choices about what's most important to spend it on. A budget helps us make these smart choices."
      },
      subtopic2: {
        title: "Why have a budget?",
        text: [
          "A budget is important because:",
          "• Track our Spending - It shows us exactly how much money we have and where it's being used",
          "• Save for Goals - If you want something special, a budget helps you save a little bit at a time",
          "• Avoid Money Problems - Spending more than you have can lead to difficulties. A budget helps us stay within our limits"
        ]
      },
      questions: [
        { q: "What is a budget?", options: ["A type of bank account", "A guide for your money", "A way to earn more money", "A list of things you want to buy"], correct: 1 },
        { q: "What does a budget help you do?", options: ["Spend all your money at once", "Know how much money is coming in and where it goes", "Hide your money from others", "Only buy expensive things"], correct: 1 },
        { q: "Why is it important to track how much money you have?", options: ["So you can spend it all quickly", "Because money is limited, and you need to make smart choices", "To impress your friends", "Because banks require it"], correct: 1 },
        { q: "What is the first step in creating a budget?", options: ["Spending all your money", "Knowing how much money you have coming in", "Ignoring your expenses", "Borrowing more money"], correct: 1 },
        { q: "How does a budget help with future goals?", options: ["By letting you spend everything now", "By helping you save for things you want later", "By making money disappear", "By preventing you from ever spending"], correct: 1 },
        { q: "Why is tracking spending an important part of budgeting?", options: ["It helps you spend more money carelessly", "It shows exactly how much money you have and where it's being used", "It prevents you from ever needing to save", "It makes budgeting more confusing"], correct: 1 },
        { q: "How does a budget help with saving for goals?", options: ["By allowing you to spend all your money at once", "By helping you save a little bit at a time toward your goal", "By making your goals less important", "By forcing you to take out loans"], correct: 1 },
        { q: "What is a common result of not having a budget?", options: ["Increased financial awareness", "Better control over spending", "Difficulty managing money and potential debt", "Automatic wealth accumulation"], correct: 2 },
        { q: "Why is it important to stay within budget limits?", options: ["To avoid financial stress and overspending", "To ensure you always need loans", "To make saving impossible", "To spend money as quickly as possible"], correct: 0 },
        { q: "What is one way a budget helps avoid money problems?", options: ["By encouraging you to spend beyond your limits", "By helping you stay within your financial limits", "By hiding your expenses from you", "By making all purchases free"], correct: 1 }
      ]
    }
  },

  {
    id: 2,
    activity_key: 'task_2',
    title: "Tracking Income & Expenses",
    icon: "📊",
   color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What is Income?",
        text: [
          "Income refers to the money you receive. It can come from different places, such as:",
          "• Allowance (money from parents)",
          "• Gifts (money received from occasions)",
          "• Jobs (like babysitting, dog walking, or a paper route)",
          "• Selling things (old toys, crafts, or lemonade)"
        ],
        subtitle: "What are expenses?",
        subtext:[
          "Expenses are the things you spend money on. These can be:",
          "• <strong>Needs:</strong> Essential items required for daily living, such as meals or school supplies.",
          "• <strong>Wants:</strong> Things you like but don’t need, like video games or candy",
          "• <strong>Savings:</strong> Funds intentionally setting aside for future use, like for a new bike or college",
        ],
        subtitle2: "What are savings?",
        subtext2:
          "Savings are different from expenses. Instead of spending money, you set it aside for future use — like saving for a new bike or college."
      },
      subtopic2: {
        title: "The Flow of Money",
        text: [
          "It is essential to recognize the flow of money: income represents the inflow, and expenses represent the outflow.",
          "Furthermore, identifying between needs and wants is a key aspect of managing expenses effectively. Needs are essential for our well-being, while wants are discretionary."
        ]
      },
      questions: [
        { q: "What is income?", options: ["Money you spend on toys", "Money you receive from different sources", "Money you save in a piggy bank", "Money you borrow from friends"], correct: 1 },
        { q: "Which of the following is NOT a source of income?", options: ["Allowance from parents", "Money from selling old toys", "Buying a video game", "Earnings from a paper route"], correct: 2 },
        { q: "What is an expense?", options: ["Money you earn from a job", "Money you receive as a gift", "Money you spend on goods or services", "Money you save for the future"], correct: 2 },
        { q: "What are savings?", options: ["Spending all your money on snacks", "Putting money aside for a future purchase", "Borrowing money from a friend", "Buying clothes every week"], correct: 1 },
        { q: "Why is saving money important?", options: ["So you can spend it all immediately", "To have funds for future needs or wants", "To avoid earning more income", "Because expenses are not necessary"], correct: 1 },
        { q: "What does 'income' represent in the flow of money?", options: ["Money going out for spending", "Money coming in from various sources", "Money saved in a bank", "Money borrowed from others"], correct: 1 },
        { q: "What is the key difference between a 'need' and a 'want'?", options: ["Needs are optional, while wants are required", "Needs are essential for well-being, while wants are not", "Wants are always more expensive than needs", "Needs are only for adults, while wants are for kids"], correct: 1 },
        { q: "Why is it important to distinguish between needs and wants?", options: ["To spend all money on fun activities", "To manage expenses effectively and prioritize essentials", "To avoid earning any income", "To ignore saving money"], correct: 1 },
        { q: "What is the best financial habit when managing money?", options: ["Spending all income on wants", "Ignoring needs and focusing on savings", "Balancing income, expenses, and savings wisely", "Never spending money on needs"], correct: 2 },
        { q: "Which of the following best describes an 'expense'?", options: ["Money earned from a job", "Money received as a gift", "Money spent on goods or services", "Money saved for future use"], correct: 2 }
      ]
    }
  },

  {
    id: 3,
    activity_key: 'task_3',
    title: "Savings - Why Save?",
    icon: "🏛️",
    color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What is Savings?",
        text: [
          "Savings means keeping money aside for later instead of spending it all now.",
          "",
          "Why Save?",
          "• For emergencies (like a broken bike or sudden expense)",
          "• For big goals (a new video game, a school trip, or even college)",
          "• To avoid borrowing too much (which can lead to debt)"
        ]
      },
      subtopic2: {
        title: "How to Save?",
        text: [
          "• Put money in a piggy bank or a bank account",
          "• Spend less on things you don't really need",
          "• Earn extra money (like doing chores or a small job)",
          "",
          "The practice of saving is fundamental to financial well-being. Furthermore, establishing clear financial goals provides direction and motivation for saving."
        ]
      },
      questions: [
        { q: "What does 'savings' mean?", options: ["Spending all your money right away", "Keeping money aside for later instead of spending it now", "Borrowing money from friends", "Only using cash, not cards"], correct: 1 },
        { q: "Why is saving important for emergencies?", options: ["To buy luxury items", "To have money for unexpected expenses (like a broken bike)", "To impress friends", "To avoid earning more money"], correct: 1 },
        { q: "What is the main idea behind saving money?", options: ["To never use money at all", "To be prepared for future needs and avoid financial problems", "To spend it as fast as possible", "To only use money for fun activities"], correct: 1 },
        { q: "How does saving help with borrowing?", options: ["It makes borrowing unnecessary in some cases", "It forces you to borrow more", "It makes debt unavoidable", "It has no effect on borrowing"], correct: 0 },
        { q: "What is a possible consequence of not saving?", options: ["Always having extra money", "Needing to borrow too much, leading to debt", "Never needing money again", "Getting free things forever"], correct: 1 },
        { q: "What is one simple way to start saving money?", options: ["Spend all your money immediately", "Put money in a piggy bank or bank account", "Borrow money from friends", "Ignore your expenses"], correct: 1 },
        { q: "How can you save more money each month?", options: ["Spend more on unnecessary things", "Avoid tracking your expenses", "Spend less on things you don't really need", "Never check your bank balance"], correct: 2 },
        { q: "What is a good way to earn extra money for savings?", options: ["Waiting for someone to give you money", "Doing chores or a small job", "Gambling all your savings", "Ignoring opportunities to earn"], correct: 1 },
        { q: "Why is it important to have clear financial goals when saving?", options: ["Because goals make saving feel pointless", "Because they help you stay motivated", "Because they force you to spend more", "Because they are only for adults, not kids"], correct: 1 },
        { q: "Why is saving money fundamental to financial well-being?", options: ["Because it allows you to waste money freely", "Because it helps you avoid debt and prepares you for emergencies", "Because it makes you spend more", "Because it prevents you from ever needing money"], correct: 1 }
      ]
    }
  },

  {
    id: 4,
    activity_key: 'task_4',
    title: "Emergency Fund",
    icon: "🏥",
   color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What is an Emergency Fund?",
        text: [
          "An emergency fund is money you save for unexpected problems - like when your pet gets sick, your bike breaks, or your family has a big unexpected bill.",
          "",
          "Your emergency fund should be equal to at least 3-6 months of your living expenses.",
          "",
          "Importance of Emergency Fund:",
          "• Financial Security - It provides a financial buffer during unexpected hardships",
          "• Debt Avoidance - It minimizes the need to avail a loan to address emergencies"
        ]
      },
      subtopic2: {
        title: "How to Build One - The 50-30-20 Rule",
        text: [
          "• 50% Needs - Half of your money goes to important stuff, like school supplies, lunch money, or helping with family bills",
          "• 30% Wants - A little less than half is for the things you enjoy - like games, treats, or movie tickets",
          "• 20% Savings - This includes your emergency fund and other goals!",
          "",
          "Where do you keep it? In a safe and readily accessible place, like a piggy bank or a real bank account.Establishing and maintaining an emergency fund is a smart way to keep your money safe and help you bounce back when unexpected problems happen. "
        ]
      },
      questions: [
        { q: "What is an emergency fund?", options: ["Money saved for vacations", "A savings account for unexpected expenses", "An investment in the stock market", "A loan from the bank"], correct: 1 },
        { q: "Which of the following is an example of an emergency fund use?", options: ["Buying a new video game", "Paying for a sudden medical bill", "Going out to eat at a restaurant", "Shopping for new clothes"], correct: 1 },
        { q: "How much should you ideally save in an emergency fund?", options: ["1-2 months of expenses", "3-6 months of expenses", "Just enough for one week", "More than a year's salary"], correct: 1 },
        { q: "If your salary is 3000 zed per month, which of the following would be the minimum appropriate goal for your Emergency Fund?", options: ["1000 zed", "2000 zed", "4000 zed", "9000 zed"], correct: 3 },
        { q: "If your monthly salary is 2000 zed, what is the minimum recommended amount you should save for your Emergency Fund?", options: ["2000 zed", "4000 zed", "6000 zed", "8000 zed"], correct: 2 },
        { q: "What is the 50-30-20 rule used for?", options: ["Dividing study time", "Budgeting money", "Choosing a diet plan", "Organizing a closet"], correct: 1 },
        { q: "According to the 50-30-20 rule, what percentage should go toward 'needs'?", options: ["20%", "30%", "50%", "10%"], correct: 2 },
        { q: "How much of your money should go toward 'wants' in the 50-30-20 rule?", options: ["50%", "20%", "30%", "10%"], correct: 2 },
        { q: "What percentage of your money should be saved according to the 50-30-20 rule?", options: ["50%", "30%", "20%", "10%"], correct: 2 },
        { q: "Where is a good place to keep your savings?", options: ["Under your mattress", "A piggy bank or bank account", "In a toy box", "In your school bag"], correct: 1 }
      ]
    }
  },

  {
    id: 5,
    activity_key: 'task_5',
    title: "Introduction to Banking",
    icon: "🌻",
   color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What's a Bank?",
        text: [
          "Imagine a bank as a giant, super-strong safe that keeps your money secure. It's a place where people and businesses can:",
          "• Keep their money safe - Ensuring the security of money entrusted to its care",
          "• Borrow money - Providing credit to individuals and entities, with an agreement for repayment",
          "• Enabling Financial Transactions - Offering services for the movement and exchange of money",
          "So, a bank is like a central hub for all sorts of money-related activities.",
          "Basic Banking Transactions:",
          "Here are some basic things you can do at a bank:",
          "• Deposits - Putting money in to keep it safe",
          "• Withdraw - Taking money out when you need it",
          "• Transfer - Moving money to someone else"
        ]
      },
      subtopic2: {
        title: "Different Ways to Pay",
        text: [
          "There are several ways to make transactions:",
          "• Manual Payments - Requires you to actively initiate each transaction yourself, whether by cash, check, online transfer, or card, rather than having payments process automatically.",
          "• Debit Payments - Using a card or your phone for fast, digital payments",
          "• Direct Authorization - Setting up automatic payments for things like subscriptions",
          "",
          "Why tracking transactions matters:",
          "Keeping track of your money is super important! Tracking and reconciling transactions means:",
          "a) Recording every deposit, withdrawal, and transfer",
          "b) Comparing your records with the bank’s statement to catch mistakes",
          "c) Preventing Fraud",
          "Banks keep your money safe and offer different ways to manage and pay with it. Knowing these basics helps you become smart with your money!",
        ]
      },
      questions: [
        { q: "What is the primary function of a bank?", options: ["Selling groceries", "Keeping money safe and enabling financial transactions", "Building houses", "Providing medical services"], correct: 1 },
        { q: "Which of the following is NOT a service provided by banks?", options: ["Depositing money", "Borrowing money", "Cooking meals", "Transferring money"], correct: 2 },
        { q: "What does a bank do when you deposit money?", options: ["Burns it for energy", "Keeps it secure for you", "Gives it away to strangers", "Turns it into toys"], correct: 1 },
        { q: "What is a withdrawal in banking?", options: ["Adding money to your account", "Taking money out of your account", "Donating money to charity", "Lending money to the bank"], correct: 1 },
        { q: "What is a bank transfer?", options: ["Moving money from one account to another", "Throwing money into a river", "Keeping money locked forever", "Exchanging money for candy"], correct: 0 },
        { q: "Which of the following is an example of a manual payment method?", options: ["Debit card", "Mobile payment", "Check", "Automatic bill payment"], correct: 2 },
        { q: "What is a key feature of debit payments?", options: ["They require writing a check", "They involve digital transactions using a card or phone", "They can only be used in physical stores", "They are always free of fees"], correct: 1 },
        { q: "What does 'direct authorization' in payments refer to?", options: ["Paying with cash every time", "Setting up automatic payments for recurring bills", "Manually approving every transaction", "Only using checks for payments"], correct: 1 },
        { q: "Why is tracking transactions important?", options: ["To avoid spending any money", "To record deposits, withdrawals, and transfers for accuracy", "Only businesses need to track transactions", "It's not necessary with digital payments"], correct: 1 },
        { q: "What is the purpose of reconciling transactions with a bank statement?", options: ["To ignore bank records", "To catch mistakes or fraud by comparing personal records with the bank's", "To delete old transactions", "To avoid paying bills"], correct: 1 }
      ]
    }
  },

  {
    id: 6,
    activity_key: 'task_6',
    title: "Simple Interest",
    icon: "📈",
    color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "What is simple interest? Money That Grows on Its Own? How Does That Work?",
        text: [
          "Imagine you plant a money seed (your savings) in a special pot (a bank account). The bank is like a gardener who gives your seed a little extra 'growth boost' over time. That 'growth boost' is called interest.",
          "",
          "Simple interest is like the bank giving you a little extra money to keep your savings with them. The bank pays you a little extra, and that extra amount is based on how much you originally saved.",
          "",
          "Let's say you put 100 zeds in the bank, and the bank gives you 10% simple interest each year.",
          "• After one year, you don't just have your original 100 zeds.",
          "• The bank adds 10% of 100 zeds, which is 10 zeds, to your account.",
          "• So, you now have 110 zeds Your money grew!"
        ]
      },
      subtopic2: {
        title: "Understanding Simple Interest",
        text: [
          "It's important to remember that with simple interest, the 'growth boost' is always based on the original amount you saved (the 100 zeds in our example).",
          "",
          "Simple interest is like a small thank you from the bank that helps your savings slowly grow over time. Save more, get a little more!"
        ]
      },
      questions: [
        { q: "What is simple interest?", options: ["Money that grows exponentially over time", "A fixed percentage earned only on the original amount saved", "Interest that changes based on market conditions", "A fee charged by banks for holding your money"], correct: 1 },
        { q: "In the analogy given, what does the 'money seed' represent?", options: ["The bank's profits", "Your original savings", "The interest earned", "A loan taken from the bank"], correct: 1 },
        { q: "Who is compared to a 'gardener' in the analogy?", options: ["The government", "The bank", "The account holder", "A financial advisor"], correct: 1 },
        { q: "How does simple interest work?", options: ["It compounds over time, earning interest on interest", "It is calculated only on the initial principal amount", "It fluctuates based on stock market performance", "It decreases over time"], correct: 1 },
        { q: "What determines the amount of simple interest earned?", options: ["The bank's policies only", "The original amount saved and the interest rate", "The stock market's performance", "The length of time the money is hidden under a mattress"], correct: 1 },
        { q: "How does the bank reward savers with simple interest?", options: ["By giving a bonus at the end of the year", "By paying a small percentage based on the original deposit", "By matching the savings amount", "By offering random cash prizes"], correct: 1 },
        { q: "If you save more money in a simple interest account, what happens?", options: ["The interest rate decreases", "You earn a little more interest because the principal is larger", "The bank charges a fee", "The interest becomes compound"], correct: 1 },
        { q: "What is the 'growth boost' referring to?", options: ["The bank's promotional offers", "The extra money earned as interest", "Government subsidies", "Inflation adjustments"], correct: 1 },
        { q: "Why is simple interest considered predictable?", options: ["Because it changes with the economy", "Because it is always the same amount each year", "Because it depends on stock market performance", "Because banks can adjust it anytime"], correct: 1 },
        { q: "With simple interest, how much interest do you earn each year?", options: ["Less and less each year", "The same amount every year", "Interest is only paid in the first year", "More and more each year"], correct: 1 }
      ]
    }
  },

  {
    id: 7,
    activity_key: 'task_7',
    title: "Fixed vs Variable Expenses",
    icon: "⚖️",
   color: "from-emerald-400 to-teal-500",
    content: {
      subtopic1: {
        title: "Fixed Expenses",
        text: [
          "Do you notice that some of the things you spend money on cost the same amount every time, while others change? Let's explore these differences!",
          "Money That Stays the Same: Fixed Expenses",
          "Some of your expenses are like steady, unchanging costs. These are called fixed expenses. They cost the same amount regularly, like:",
          "• Transportation Ticket - If you take the same bus or train every day, the fare is usually the same",
          "• Allowance for School Supplies - If your parents give you a set amount each week for school needs, that's fixed.",
          "• Subscription Fee - If you pay a set amount each month for a game or app",
          "",
          "So, fixed expenses are the costs you can count on being the same."
        ]
      },
      subtopic2: {
        title: "Variable Expenses",
        text: [
          "Money That Changes: Variable Expenses",
          "Other expenses change and aren't always the same. These are called variable expenses. They go up and down, like:",
          "",
          "• Snacks - How much you spend on snacks depends on how hungry you are each day!",
          "• Entertainment - Going to the movies or playing games with friends costs different amounts each time",
          "• Gifts - Buying presents for birthdays or holidays varies depending on who you're buying for",
          "",
          "So, variable expenses are the costs that change depending on what you do or need.",
          "Knowing the difference between fixed expenses (staying the same) and variable expenses (changing) is super helpful for planning your money. You can predict your fixed costs, but you need to be flexible with your variable costs. This helps you budget smarter and avoid money surprises!"
        ]
      },
      questions: [
        { q: "What are fixed expenses?", options: ["Costs that change frequently", "Costs that stay the same regularly", "Costs that only occur once a year", "Costs that are unnecessary"], correct: 1 },
        { q: "Why are fixed expenses easier to plan for?", options: ["Because they change every month", "Because they are unexpected", "Because they cost the same amount each time", "Because they are rarely paid"], correct: 2 },
        { q: "What makes a subscription fee a fixed expense?", options: ["It changes based on usage", "It is paid only once", "It is the same amount every billing cycle", "It is optional"], correct: 2 },
        { q: "Which of the following best describes fixed expenses?", options: ["Unpredictable and irregular", "Consistent and recurring", "Rare and unexpected", "Flexible and adjustable"], correct: 1 },
        { q: "Why is it helpful to identify fixed expenses in a budget?", options: ["Because they can be ignored", "Because they make budgeting unpredictable", "Because they allow for stable financial planning", "Because they increase every month"], correct: 2 },
        { q: "What are variable expenses?", options: ["Costs that stay the same every month", "Costs that change depending on usage or needs", "Costs that are paid only once a year", "Costs that are unnecessary"], correct: 1 },
        { q: "Why are variable expenses harder to predict than fixed expenses?", options: ["Because they are always the same", "Because they change based on different factors", "Because they are paid only once", "Because they don't affect budgeting"], correct: 1 },
        { q: "A variable expense differs from a fixed expense because:", options: ["It is always cheaper", "The amount can fluctuate", "It is paid only once", "It is not important for budgeting"], correct: 1 },
        { q: "Which of the following best describes variable expenses?", options: ["Predictable and steady", "Changing and flexible", "Rare and fixed", "Unnecessary and avoidable"], correct: 1 },
        { q: "Why is it important to track variable expenses in a budget?", options: ["Because they don't affect spending habits", "Because they help avoid overspending", "Because they are always the same", "Because they don't need planning"], correct: 1 }
      ]
    }
  }
];
