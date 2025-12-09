import React, { useState } from 'react';

export default function RefreshButton({ tvdbSeriesId, refreshState, size = "md", variant = "outline-info", className = "" }) {
    const [isRefreshing, setIsRefreshing] = useState(false);

    const handleRefresh = async () => {
        if (isRefreshing) return;
        
        setIsRefreshing(true);
        
        try {
            const response = await fetch(`/api/series/${tvdbSeriesId}/refresh`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to refresh series');
            }

            const data = await response.json();
            
            // Refresh the parent component to show updated data
            if (refreshState) {
                refreshState();
            }
        } catch (error) {
            console.error('Error refreshing series:', error);
            alert(`Failed to refresh series: ${error.message}`);
        } finally {
            setIsRefreshing(false);
        }
    };

    return (
        <button
            className={`btn btn-${size} btn-${variant} ${className}`}
            onClick={handleRefresh}
            disabled={isRefreshing}
            type="button"
            title="Refresh metadata from TVDB"
        >
            {isRefreshing ? (
                <>
                    <span className="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Refreshing...
                </>
            ) : (
                <>
                    <i className="bi bi-arrow-clockwise"></i> Refresh
                </>
            )}
        </button>
    );
}
