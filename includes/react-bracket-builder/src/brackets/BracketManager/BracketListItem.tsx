import React, { useState, useEffect } from 'react'
import './BracketManager.scss'
import { bracketApi } from '../shared/api/bracketApi'
import { BracketRes } from '../shared/api/types/bracket'
import linkIcon from '../shared/assets/link.svg'
import copyIcon from '../shared/assets/copy.svg'
import trashIcon from '../shared/assets/trash.svg'
import playIcon from '../shared/assets/play.svg'
import trophyIcon from '../shared/assets/trophy.svg'
import liveIcon from '../shared/assets/Frame.png'
import barChartIcon from '../shared/assets/bar-chart.svg'
import lockIcon from '../shared/assets/lock.svg'
import trendUpIcon from '../shared/assets/trend-up-leaderboard.svg'
import shareIcon from '../shared/assets/share.svg'

const createLink = () => {

}

const createCopy = () => {

}

const deleteBracket = (bracketId: number) => {
    const response = bracketApi.deleteBracket(bracketId)
    console.log(response)
}

const BracketsListItem = () => {

    const [brackets, setBrackets] = useState<BracketRes[]>([])
    let bracketId: number

    const bracketActionIcons = [
        { src: linkIcon, onClick: createLink },
        { src: copyIcon, onClick: () => createCopy() },
        { src: trashIcon, onClick: () => deleteBracket(bracketId) }
    ];

    const bracketStateIcons = [
        { src: liveIcon },
        { src: barChartIcon },
        { src: lockIcon }
    ];

    useEffect(() => {
        bracketApi.getUserBrackets().then((brackets) => {
            setBrackets(brackets)
        });
    }, []);

    return (
        <div>
            {brackets.map((bracket) => {
                return (
                    <div className='bracket-list'>
                        <div className='bracket-details'>
                            <div>
                                <span className='bracket-name text-uppercase'>{bracket.name}</span>
                                {bracketActionIcons.map((icon, index) => (
                                    <span key={index} className='bracket-action-images rounded-border'>
                                        <img src={icon.src} onClick={icon.onClick} />
                                    </span>
                                ))}
                            </div>
                            <div>
                                <span className='play-bracket-btn rounded-border'>
                                    <img src={playIcon} className='play-set-btn' />
                                    <button className='play-bracket text-uppercase'>Play Bracket</button>
                                </span>
                                <span className='set-bracket-score-btn rounded-border'>
                                    <img src={trophyIcon} className='play-set-btn' />
                                    <button className='set-bracket-score text-uppercase'>set Bracket Score</button>
                                </span>
                            </div>
                        </div>
                        <div className='bracket-state-details'>
                            <div>
                                {bracketStateIcons.map((icon, index) => (
                                    <span key={index} className='bracket-state-images'>
                                        <img src={icon.src} alt={`Icon ${index}`} />
                                    </span>
                                ))}
                                <span className='private-bracket text-uppercase'>Private Bracket</span>
                            </div>
                            <div className='uptrend-leaderBoard'>
                                <span className='view-leaderboard-btn rounded-border'>
                                    <img src={trendUpIcon} className='play-set-btn' />
                                    <button className='view-leaderboard text-uppercase'>View LeaderBoard</button>
                                </span>
                                <span className='share-bracket-btn rounded-border'>
                                    <img src={shareIcon} className='play-set-btn' />
                                    <button className='share-bracket text-uppercase'>Share Bracket</button>
                                </span>
                            </div>
                        </div>
                    </div>
                )
            })}
        </div>
    )

}

export default BracketsListItem
