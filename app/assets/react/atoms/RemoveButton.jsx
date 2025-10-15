import React from 'react'

export default function RemoveButton({id, refreshState, size = "lg", variant = "danger", className = ""}) {
    const handleClick = () => {
        const removeSeries = fetch('/api/series/' + id, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/delete+json"
            }
        }).then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
        }).finally(() => {
            refreshState();
        });
    };

    return (
        <div className="component text-center" id="remove">
            <button 
                className={`btn btn-${size} ${size === 'lg' ? 'btn-block' : ''} btn-${variant} ${className}`}
                type="button" 
                onClick={handleClick}
            >
                Remove Series
            </button>
        </div>
    )
}
