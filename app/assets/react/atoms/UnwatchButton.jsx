import React from 'react'

export default function UnwatchButton({id, refreshState, size = "lg", variant = "warning", className = ""}) {
    const handleClick = () => {
        // First, unmark the episode as watched
        const unwatchedEpisode = fetch('/api/episodes/' + id, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/merge-patch+json"
            },
            body: JSON.stringify({
                watched: false
            })
        })
        .then((response) => {
            if (!response.ok) {
                console.error(`Failed to unmark episode ${id}:`, response.status, response.statusText);
                throw new Error(`Failed to unmark episode: ${response.status}`);
            }
            return response.json();
        })
        .catch((error) => {
            console.error(`Error unmarking episode ${id}:`, error);
            throw error;
        });

        // Then, find and delete the corresponding history entry
        unwatchedEpisode.then(episode => {
            // Get all histories and find the one matching this episode
            return fetch('/api/histories', {
                method: "GET",
                headers: {
                    "Content-Type": "application/ld+json"
                }
            })
            .then(response => {
                if (!response.ok) {
                    console.error(`Failed to fetch histories for episode ${id}:`, response.status);
                    throw new Error(`Failed to fetch histories: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Find the history entry for this episode
                const historyEntry = data['hydra:member'].find(entry => 
                    entry.seriesTitle === episode.seriesTitle && 
                    entry.episodeTitle === episode.title
                );
                
                if (historyEntry) {
                    // Delete the history entry
                    return fetch('/api/histories/' + historyEntry.id, {
                        method: "DELETE"
                    })
                    .then(response => {
                        if (!response.ok) {
                            console.error(`Failed to delete History ${historyEntry.id}:`, response.status);
                            throw new Error(`Failed to delete History entry: ${response.status}`);
                        }
                        console.log(`Successfully deleted History entry ${historyEntry.id} for episode ${episode.id}`);
                        return response;
                    });
                } else {
                    console.warn(`No History entry found for episode ${episode.id} (${episode.seriesTitle} - ${episode.title})`);
                }
            });
        })
        .catch((error) => {
            console.error(`Critical error in UnwatchButton for episode ${id}:`, error);
            alert(`Failed to unwatch episode. Please try again. Error: ${error.message}`);
        })
        .finally(() => {
            refreshState();
        });
    };

    return (
        <div className="component text-center" id="unwatched">
            <button 
                className={`btn btn-${size} ${size === 'lg' ? 'btn-block' : ''} btn-${variant} ${className}`}
                type="button" 
                onClick={handleClick}
            >
                Unwatch
            </button>
        </div>
    )
}