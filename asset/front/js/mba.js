const template = window.templateData;
const { useState, useEffect } = React;
const BudgetingActivity = () => {
    const [bankItems, setBankItems] = useState(template);

    const [isAdmin, setIsAdmin] = useState(window.userRole === 4);
    const [engagementPoints, setEngagementPoints] = useState(0);
    const [draggedItem, setDraggedItem] = useState(null);
    const [showMessage, setShowMessage] = useState(null);
    const [hasCompleted, setHasCompleted] = useState(false);

    const [budget, setBudget] = useState({
        income: [
            { id: 'inc1', label: 'Paycheck', item: null },
            { id: 'inc2', label: 'Side income', item: null, hidden: false },
            { id: 'inc3', label: '', item: null, hidden: false }
        ],
        needs: Array(10).fill(null).map((_, i) => ({ id: `need${i}`, item: null })),
        wants: Array(10).fill(null).map((_, i) => ({ id: `want${i}`, item: null })),
        savings: Array(6).fill(null).map((_, i) => ({ id: `save${i}`, item: null }))
    });

    const handleFileUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            try {
                const data = new Uint8Array(event.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);

                const items = jsonData.map((row, index) => ({
                    id: String(row.ID || index + 1),
                    name: row.Name || row.name || '',
                    amount: Number(row.Amount || row.amount || 0),
                    category: (row.Category || row.category || 'needs').toLowerCase(),
                    type: (row.Type || row.type || 'expense').toLowerCase()
                }));
                console.log(items)

                setBankItems(items);
                setShowMessage({
                    type: 'success',
                    text: `Successfully loaded ${items.length} items from Excel!`
                });
                setTimeout(() => setShowMessage(null), 3000);
            } catch (error) {
                setShowMessage({
                    type: 'error',
                    text: 'Error reading Excel file. Please check the format.'
                });
                setTimeout(() => setShowMessage(null), 3000);
            }
        };
        reader.readAsArrayBuffer(file);
    };

    const downloadTemplate = () => {
        const template = [
            { ID: 1, Name: 'Monthly Salary', Amount: 3500, Category: 'needs', Type: 'income' },
            { ID: 2, Name: 'Freelance Work', Amount: 500, Category: 'needs', Type: 'income' },
            { ID: 3, Name: 'Rent Payment', Amount: -1200, Category: 'needs', Type: 'expense' },
            { ID: 4, Name: 'Groceries', Amount: -350, Category: 'needs', Type: 'expense' },
            { ID: 5, Name: 'Electricity Bill', Amount: -85, Category: 'needs', Type: 'expense' },
            { ID: 6, Name: 'Movie Tickets', Amount: -30, Category: 'wants', Type: 'expense' },
            { ID: 7, Name: 'Streaming Services', Amount: -35, Category: 'wants', Type: 'expense' },
            { ID: 8, Name: 'Restaurant', Amount: -75, Category: 'wants', Type: 'expense' },
            { ID: 9, Name: 'Savings Transfer', Amount: -800, Category: 'savings', Type: 'expense' },
            { ID: 10, Name: 'Emergency Fund', Amount: -200, Category: 'savings', Type: 'expense' }
        ];
        const ws = XLSX.utils.json_to_sheet(template);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Bank Statement');
        XLSX.writeFile(wb, 'bank_statement_template.xlsx');
    };
    /*const saveData = () => {
        console.log('save data')
        console.log(budget)
    }*/
    /*const saveData = () => {
        fetch('/education/store-mba', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                budget: budget
            })
        })
            .then(res => res.json())
            .then(data => console.log('Saved!', data));
        console.log(budget)
    };*/
    const saveData = () => {
    fetch('/education/store-mba', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            budget: budget
        })
    })
    .then(res => res.json())
    .then(data => {
        setShowMessage({
            type: data.status ? 'success' : 'error',
            text: data.message
        });

        setTimeout(() => setShowMessage(null), 3000);

        console.log('Saved!', data);
    })
    .catch(error => {
        setShowMessage({
            type: 'error',
            text: 'Something went wrong while saving.'
        });

        setTimeout(() => setShowMessage(null), 3000);

        console.error(error);
    });
};
    useEffect(() => {
        const getData = () => {
            fetch('/education/mba-data', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },

            })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        if (data.data) {
                            console.log(data.data)
                            setBudget(data.data)
                        }
                    }
                });

        };
        getData()
    }, [])


    const handleDragStart = (e, item) => {
        setDraggedItem(item);
        e.target.classList.add('dragging');
    };

    const handleDragEnd = (e) => {
        e.target.classList.remove('dragging');
        setDraggedItem(null);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
    };

    const handleDragEnter = (e) => {
        e.preventDefault();
        if (e.currentTarget.classList.contains('budget-cell')) {
            e.currentTarget.classList.add('drag-over');
        }
    };

    const handleDragLeave = (e) => {
        if (e.currentTarget.classList.contains('budget-cell')) {
            e.currentTarget.classList.remove('drag-over');
        }
    };

    const handleDrop = (e, category, cellIndex) => {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');

        if (!draggedItem) return;

        // Check if cell already has an item
        if (budget[category][cellIndex].item) {
            setShowMessage({
                type: 'error',
                text: 'This cell is already filled! Choose an empty cell.'
            });
            setTimeout(() => setShowMessage(null), 3000);
            return;
        }

        // Check if already placed
        const isAlreadyPlaced = Object.values(budget).some(categoryItems =>
            categoryItems.some(cell => cell.item && cell.item.id === draggedItem.id)
        );

        if (isAlreadyPlaced) {
            setShowMessage({
                type: 'error',
                text: 'This item has already been placed in the budget!'
            });
            setTimeout(() => setShowMessage(null), 3000);
            return;
        }

        // Validate category
        //const correctCategory = draggedItem.type === 'income' ? 'income' : draggedItem.category;
        /*const correctCategory = draggedItem.type === 'income'
            ? 'income'
            : draggedItem.category === 'penalty'
                ? 'wants'
                : draggedItem.category;*/
        /*const forceWantsCategories = ['penalty', 'donation'];

        const correctCategory = draggedItem.type === 'income'
            ? 'income'
            : forceWantsCategories.includes(draggedItem.category)
                ? 'wants'
                : draggedItem.category;*/
        const forceWantsCategories = ['penalty', 'donation'];

        const forceNeedsKeywords = [
            'utility',
            'internet',
            'rent',
            'school'
        ];

        const itemName = draggedItem.name.toLowerCase();
        const itemCategory = draggedItem.category.toLowerCase();

        const correctCategory = draggedItem.type === 'income'
            ? 'income'
            : forceWantsCategories.includes(itemCategory)
                ? 'wants'
                : forceNeedsKeywords.some(keyword => itemName.includes(keyword))
                    ? 'needs'
                    : itemCategory;

        if (category !== correctCategory) {
            setShowMessage({
                type: 'error',
                text: `Incorrect! "${draggedItem.name}" belongs in ${correctCategory.toUpperCase()}, not ${category.toUpperCase()}.`
            });
            setTimeout(() => setShowMessage(null), 3000);
            return;
        }

        // Place item
        setBudget(prev => {
            const newBudget = { ...prev };
            newBudget[category][cellIndex].item = draggedItem;
            return newBudget;
        });

        setShowMessage({
            type: 'success',
            text: `✓ Great job! "${draggedItem.name}" correctly placed!`
        });
        setTimeout(() => setShowMessage(null), 2000);

        // Check completion
        const allPlacedItems = Object.values(budget).reduce((sum, cat) =>
            sum + cat.filter(cell => cell.item).length, 0) + 1;

        if (allPlacedItems === bankItems.length && !hasCompleted) {
            setHasCompleted(true);
            setEngagementPoints(50);
            setTimeout(() => {
                setShowMessage({
                    type: 'success',
                    text: '🎉 Congratulations! You earned 50 Engagement Badge Points for completing this activity!'
                });
                setTimeout(() => setShowMessage(null), 5000);
            }, 2000);
        }
    };

    const calculateTotals = () => {
        const totalIncome = budget.income.reduce((sum, cell) =>
            sum + (cell.item ? cell.item.amount : 0), 0);
        const totalNeeds = Math.abs(budget.needs.reduce((sum, cell) =>
            sum + (cell.item ? cell.item.amount : 0), 0));
        const totalWants = Math.abs(budget.wants.reduce((sum, cell) =>
            sum + (cell.item ? cell.item.amount : 0), 0));
        const totalSavings = Math.abs(budget.savings.reduce((sum, cell) =>
            sum + (cell.item ? cell.item.amount : 0), 0));

        return {
            totalIncome,
            totalNeeds,
            totalWants,
            totalSavings,
            needsPercent: totalIncome > 0 ? ((totalNeeds / totalIncome) * 100).toFixed(1) : 0,
            wantsPercent: totalIncome > 0 ? ((totalWants / totalIncome) * 100).toFixed(1) : 0,
            savingsPercent: totalIncome > 0 ? ((totalSavings / totalIncome) * 100).toFixed(1) : 0,
        };
    };

    const totals = calculateTotals();

    const formatCurrency = (amount) => {
        return amount ? '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') : '';
    };

    const resetActivity = () => {
        setBudget({
            income: [
                { id: 'inc1', label: 'Paycheck', item: null },
                { id: 'inc2', label: 'Side income', item: null },
                { id: 'inc3', label: '', item: null }
            ],
            needs: Array(10).fill(null).map((_, i) => ({ id: `need${i}`, item: null })),
            wants: Array(10).fill(null).map((_, i) => ({ id: `want${i}`, item: null })),
            savings: Array(6).fill(null).map((_, i) => ({ id: `save${i}`, item: null }))
        });
        setHasCompleted(false);
    };

    const updateItemCategory = (itemId, newCategory) => {
        setBankItems(prev => prev.map(item =>
            item.id === itemId ? { ...item, category: newCategory } : item
        ));
    };

    const monthYear = new Date().toLocaleString('en-US', {
        month: 'long',
        year: 'numeric'
    });
    const previousMonth = new Date();
    previousMonth.setMonth(previousMonth.getMonth() - 1);

    const pmonthYear = previousMonth.toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
    });
    // Main activity
    return (
        <div>
            {/* Header */}


            {/* Message */}
            {showMessage && (
                <div style={{ maxWidth: '1600px', margin: '0 auto 16px', padding: '16px', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)', backgroundColor: showMessage.type === 'success' ? '#c8e6c9' : '#ffcdd2', color: showMessage.type === 'success' ? '#1b5e20' : '#c62828' }}>
                    <p style={{ margin: 0, fontWeight: '500' }}>{showMessage.text}</p>
                </div>
            )}

            <div style={{ maxWidth: '1600px', margin: '0 auto', display: 'grid', gridTemplateColumns: '1fr 1.5fr', gap: '24px' }}>
                {/* LEFT: Bank Statement */}
                <div style={{ backgroundColor: 'white', borderRadius: '8px', border: "1px solid #e5e7eb", padding: '16px' }}>
                    <h2 style={{ fontSize: '20px', fontWeight: 'bold', color: '#333', marginBottom: '6px' }}>BANK STATEMENT - {pmonthYear}</h2>


                    <div style={{ maxHeight: '800px', overflowY: 'auto' }}>
                        {bankItems.map((item) => {
                            const isPlaced = Object.values(budget).some(cat =>
                                cat.some(cell => cell.item && cell.item.id === item.id)
                            );

                            return (
                                <div
                                    key={item.id}
                                    draggable={!isPlaced}
                                    onDragStart={(e) => handleDragStart(e, item)}
                                    onDragEnd={handleDragEnd}
                                    className="draggable-item"
                                    style={{
                                        padding: '16px',
                                        marginBottom: '8px',
                                        borderRadius: '8px',
                                        border: '2px solid',
                                        borderColor: isPlaced ? '#ccc' : '#e0e0e0',
                                        backgroundColor: isPlaced ? '#f5f5f5' : 'white',
                                        opacity: isPlaced ? 0.5 : 1,
                                        cursor: isPlaced ? 'not-allowed' : 'move'
                                    }}
                                >
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontWeight: '500', color: '#333' }}>{item.name}</span>
                                        <span style={{ fontWeight: 'bold', color: item.amount > 0 ? '#4caf50' : '#f44336' }}>
                                            {item.amount > 0 ? '+' : ''}{formatCurrency(Math.abs(item.amount))}
                                        </span>
                                    </div>
                                    {isPlaced && <p style={{ fontSize: '12px', color: '#999', marginTop: '4px', marginBottom: 0 }}>✓ Already placed</p>}
                                    {/*{isAdmin && (
                                        <div style={{ marginTop: '8px', display: 'flex', gap: '8px' }}>
                                            <select
                                                value={item.category}
                                                onChange={(e) => updateItemCategory(item.id, e.target.value)}
                                                style={{ fontSize: '12px', padding: '4px 8px', border: '1px solid #ccc', borderRadius: '4px' }}
                                            >
                                                <option value="needs">Needs</option>
                                                <option value="wants">Wants</option>
                                                <option value="savings">Savings</option>
                                            </select>
                                            <span style={{ fontSize: '12px', padding: '4px 8px', backgroundColor: '#e0e0e0', borderRadius: '4px' }}>
                                                {item.type}
                                            </span>
                                        </div>
                                    )}*/}
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* RIGHT: Monthly Budget Worksheet */}
                <div style={{ borderRadius: '8px', border: "1px solid #e5e7eb", padding: '16px' }}>
                    <h2 style={{ fontSize: '20px', fontWeight: 'bold', textAlign: 'center', marginBottom: '6px', color: '#000' }}>MONTHLY BUDGET</h2>

                    <div className="budget-header" style={{ marginBottom: '6px', paddingTop: "0px" }}>
                        MONTH OF: {pmonthYear}
                    </div>

                    {/* Income and Needs Row */}
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '16px' }}>
                        {/* INCOME */}
                        <div className="budget-section" style={{ padding: '12px', borderRadius: '4px' }}>
                            <h3 style={{ fontWeight: 'bold', marginBottom: '8px', fontSize: '14px' }}>INCOME</h3>
                            {budget.income.map((cell, idx) => {
                                if (!cell.hidden) {
                                    return <div key={cell.id} style={{ marginBottom: '8px' }}>
                                        <div style={{ display: 'flex', gap: '8px' }}>
                                            <div style={{ flex: '0 0 90px', borderRadius: "6px", padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', fontSize: '12px', display: 'flex', alignItems: 'center' }}>
                                                {cell.label}
                                            </div>
                                            <div
                                                className="budget-cell"
                                                style={{ flex: 1, fontSize: '11px' }}
                                                onDragOver={handleDragOver}
                                                onDragEnter={handleDragEnter}
                                                onDragLeave={handleDragLeave}
                                                onDrop={(e) => handleDrop(e, 'income', idx)}
                                            >
                                                {cell.item ? (
                                                    <div style={{ width: '100%', textAlign: 'center' }}>
                                                        <div style={{ fontWeight: '500' }}>{cell.item.name}</div>
                                                        <div style={{ fontWeight: 'bold', color: '#4caf50' }}>{formatCurrency(cell.item.amount)}</div>
                                                    </div>
                                                ) : (
                                                    <div style={{ color: '#999', fontSize: '10px' }}>Drop here</div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                } else {
                                    return null
                                }
                            })}
                            {/* <div style={{ display: 'flex', gap: '8px', marginTop: '8px' }}>
                                <div style={{ flex: '0 0 90px', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', fontWeight: 'bold', fontSize: '12px' }}>Total Income</div>
                                <div style={{ flex: 1, padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', fontWeight: 'bold', fontSize: '12px', textAlign: 'center' }}>
                                    {formatCurrency(totals.totalIncome)}
                                </div>
                            </div> */}
                        </div>
                        {/* SAVINGS */}
                        <div className="budget-section" style={{ padding: '12px', borderRadius: '4px' }}>
                            <h3 style={{ fontWeight: 'bold', marginBottom: '8px', fontSize: '14px' }}>SAVINGS (20%) _____ - {totals.savingsPercent}%</h3>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '4px' }}>
                                {budget.savings.map((cell, idx) => (
                                    <div
                                        key={cell.id}
                                        className="budget-cell"
                                        style={{ fontSize: '10px', minHeight: '42px' }}
                                        onDragOver={handleDragOver}
                                        onDragEnter={handleDragEnter}
                                        onDragLeave={handleDragLeave}
                                        onDrop={(e) => handleDrop(e, 'savings', idx)}
                                    >
                                        {cell.item ? (
                                            <div style={{ width: '100%', textAlign: 'center', padding: '2px' }}>
                                                <div style={{ fontWeight: '500', fontSize: '9px', lineHeight: '1.2' }}>{cell.item.name}</div>
                                                <div style={{ fontWeight: 'bold', color: '#f44336', fontSize: '10px' }}>{formatCurrency(Math.abs(cell.item.amount))}</div>
                                            </div>
                                        ) : (
                                            <div style={{ color: '#999', fontSize: '9px' }}>Drop</div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                    </div>

                    {/* Savings and Wants Row */}
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '16px' }}>

                        {/* NEEDS */}
                        <div className="budget-section" style={{ padding: '12px', borderRadius: '4px' }}>
                            <h3 style={{ fontWeight: 'bold', marginBottom: '8px', fontSize: '14px' }}>NEEDS (50%) _____ - {totals.needsPercent}%</h3>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '4px' }}>
                                {budget.needs.map((cell, idx) => (
                                    <div
                                        key={cell.id}
                                        className="budget-cell"
                                        style={{ fontSize: '10px', minHeight: '42px' }}
                                        onDragOver={handleDragOver}
                                        onDragEnter={handleDragEnter}
                                        onDragLeave={handleDragLeave}
                                        onDrop={(e) => handleDrop(e, 'needs', idx)}
                                    >
                                        {cell.item ? (
                                            <div style={{ width: '100%', textAlign: 'center', padding: '2px' }}>
                                                <div style={{ fontWeight: '500', fontSize: '9px', lineHeight: '1.2' }}>{cell.item.name}</div>
                                                <div style={{ fontWeight: 'bold', color: '#f44336', fontSize: '10px' }}>{formatCurrency(Math.abs(cell.item.amount))}</div>
                                            </div>
                                        ) : (
                                            <div style={{ color: '#999', fontSize: '9px' }}>Drop</div>
                                        )}
                                    </div>
                                ))}
                            </div>
                            <div style={{ marginTop: '8px', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', fontWeight: 'bold', textAlign: 'center', fontSize: '12px' }}>
                                TOTAL: {formatCurrency(totals.totalNeeds)}
                            </div>
                        </div>
                        {/* WANTS */}
                        <div className="budget-section" style={{ padding: '12px', borderRadius: '4px' }}>
                            <h3 style={{ fontWeight: 'bold', marginBottom: '8px', fontSize: '14px' }}>WANTS (30%) _____ - {totals.wantsPercent}%</h3>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '4px' }}>
                                {budget.wants.map((cell, idx) => (
                                    <div
                                        key={cell.id}
                                        className="budget-cell"
                                        style={{ fontSize: '10px', minHeight: '42px' }}
                                        onDragOver={handleDragOver}
                                        onDragEnter={handleDragEnter}
                                        onDragLeave={handleDragLeave}
                                        onDrop={(e) => handleDrop(e, 'wants', idx)}
                                    >
                                        {cell.item ? (
                                            <div style={{ width: '100%', textAlign: 'center', padding: '2px' }}>
                                                <div style={{ fontWeight: '500', fontSize: '9px', lineHeight: '1.2' }}>{cell.item.name}</div>
                                                <div style={{ fontWeight: 'bold', color: '#f44336', fontSize: '10px' }}>{formatCurrency(Math.abs(cell.item.amount))}</div>
                                            </div>
                                        ) : (
                                            <div style={{ color: '#999', fontSize: '9px' }}>Drop</div>
                                        )}
                                    </div>
                                ))}
                            </div>
                            <div style={{ marginTop: '8px', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', fontWeight: 'bold', textAlign: 'center', fontSize: '12px' }}>
                                TOTAL: {formatCurrency(totals.totalWants)}
                            </div>
                        </div>
                    </div>

                    {/* MONTHLY SUMMARY */}
                    <div className="budget-section" style={{ padding: '12px', display: "none", borderRadius: '4px', marginBottom: '16px' }}>
                        <h3 style={{ fontWeight: 'bold', marginBottom: '8px', textAlign: 'center', fontSize: '14px' }}>MONTHLY</h3>
                        <div style={{ fontSize: '12px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', marginBottom: '4px' }}>
                                <span>Total Income</span>
                                <span style={{ fontWeight: 'bold' }}>{formatCurrency(totals.totalIncome)}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', marginBottom: '4px' }}>
                                <span>Total Needs</span>
                                <span style={{ fontWeight: 'bold' }}>- {formatCurrency(totals.totalNeeds)}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', marginBottom: '4px' }}>
                                <span>Total Wants</span>
                                <span style={{ fontWeight: 'bold' }}>- {formatCurrency(totals.totalWants)}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px', border: '1px solid #00a47d', backgroundColor: '#d4f1d4', marginBottom: '4px' }}>
                                <span>Total Savings</span>
                                <span style={{ fontWeight: 'bold' }}>- {formatCurrency(totals.totalSavings)}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', padding: '8px', border: '2px solid #00a47d', backgroundColor: '#b8ddb8', fontWeight: 'bold' }}>
                                <span>TOTAL BUDGET</span>
                                <span>{formatCurrency(totals.totalIncome - totals.totalNeeds - totals.totalWants - totals.totalSavings)}</span>
                            </div>
                        </div>
                    </div>

                    {/* NOTES */}
                    <div style={{ backgroundColor: '#e8e8e8', display: "none", padding: '12px', borderRadius: '4px', border: '2px solid #999' }}>
                        <h3 style={{ fontWeight: 'bold', textAlign: 'center', marginBottom: '8px', fontSize: '14px' }}>Notes</h3>
                        <div style={{ height: '80px', backgroundColor: 'white', borderRadius: '4px', border: '2px solid #ccc' }}></div>
                    </div>
                    <div style={{ justifyContent: "end" }} class="button-group">
                        <button class="themeBtn" onClick={() => saveData()} >Complete</button>
                    </div>
                </div>
            </div>
        </div>
    );
};

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<BudgetingActivity />);