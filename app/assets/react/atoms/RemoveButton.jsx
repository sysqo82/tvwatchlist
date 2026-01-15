import React from 'react'

export default function RemoveButton({id, refreshState, size = "lg", variant = "danger", className = "", type = "series"}) {
    const handleClick = () => {
        const itemType = type === 'movie' ? 'movie' : 'series';
        const confirmMessage = `Are you sure you want to remove this ${itemType} from your watchlist? It will be moved to the archive.`;
        
        // Add confirmation dialog to prevent accidental removal
        if (!window.confirm(confirmMessage)) {
            return;
        }

        // Archive endpoint for both movies and series
        const endpoint = type === 'movie' ? `/api/movies/${id}/archive` : `/api/series/${id}`;
        const method = type === 'movie' ? 'POST' : 'DELETE';
        
        const removeItem = fetch(endpoint, {
            method: method,
            headers: {
                "Content-Type": "application/json"
            }
        }).then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
        }).finally(() => {
            refreshState();
        });
    };

    const buttonText = type === 'movie' ? 'Archive Film' : 'Archive Series';

    return (
        <div className="component text-center" id="remove">
            <button 
                className={`btn btn-${size} ${size === 'lg' ? 'btn-block' : ''} btn-${variant} ${className}`}
                type="button" 
                onClick={handleClick}
            >
                {buttonText}
            </button>
        </div>
    )
}
