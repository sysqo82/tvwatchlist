import React from 'react'

export default function UnwatchButton({id, refreshState, style}) {
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
                throw new Error("Network response was not ok");
            }
            return response.json();
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
            .then(response => response.json())
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
                    });
                }
            });
        }).finally(() => {
            refreshState();
        });
    };

    return (
        <div className="component text-center" id="unwatched">
            <button 
                className="btn btn-lg btn-block btn-warning" 
                type="button" 
                onClick={handleClick}
                style={style}
            >
                Unwatch
            </button>
        </div>
    )
}