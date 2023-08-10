import React from 'react'
import './BracketManager.scss'
import BracketsDisplay from './BracketsDisplay'
import bracketIcon from '../shared/assets/BMB-ICON-WHITE.png'
import plusIcon from '../shared/assets/plus.svg'

const BracketManager = () => {

    return (
        <div className='wpbb-bracket-manager-root'>
            <div className='our-picks rounded-border'></div>
            <div className='new-bracket rounded-border'>
                <span className='new-bracket-images'>
                    <img src={bracketIcon} alt="" />
                </span>
                <span className='new-bracket-images'>
                    <img src={plusIcon} alt="" />
                </span>
                <span className='new-bracket-text text-uppercase'>new  bracket</span>
            </div>
            <div className='my-brackets'>
                <div className='my-brackets-text text-uppercase'>
                    My Brackets
                </div>
            </div>
            <div className='brackets-list'>
                <BracketsDisplay />
            </div>
        </div>
    )
}

export default BracketManager