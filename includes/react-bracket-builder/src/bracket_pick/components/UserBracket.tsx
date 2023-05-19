import React, { useState, useEffect, useRef } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';

import { MatchTree, WildcardPlacement } from '../../bracket/models/MatchTree';
import { BracketRes } from '../../api/types/bracket';



interface BuyApparelBtnProps {
	onClick?: () => void;
	disabled?: boolean;
}

const ApparelButton = (props: BuyApparelBtnProps) => {
	const {
		disabled = false,
		onClick = () => { }
	} = props;
	return (
		<button className={'wpbb-apparel-btn' + (disabled ? '-disabled' : '')} onClick={onClick} disabled={disabled}>
			ADD TO APPAREL
		</button>
	)
}

interface UserBracketProps {
	bracketId?: number;
	bracketRes?: BracketRes;
}

const UserBracket = (props: UserBracketProps) => {
	const {
		bracketId,
		bracketRes
	} = props;

	const [matchTree, setMatchTree] = useState<Nullable<MatchTree>>(null);
	const bracketRef = useRef<HTMLDivElement>(null)

	useEffect(() => {
		if (bracketId) {
			bracketApi.getBracket(bracketId).then((res) => {
				setMatchTree(MatchTree.fromRounds(res.rounds));
			});
		} else if (bracketRes) {
			setMatchTree(MatchTree.fromRounds(bracketRes.rounds));
		}
	}, [bracketId, bracketRes]);

	const getImage = () => {
		const bracketEl: HTMLDivElement | null = bracketRef.current
		if (!bracketEl) {
			return
		}
		const bracketHTML = bracketEl.outerHTML
		console.log(bracketHTML)
		// const userBracket = matchTree.toUserRequest('barry bracket', 999);
		// const json = JSON.stringify(userBracket);
		// console.log(json)
	}

	const handleApparelClick = () => {
		console.log('apparel click')
		getImage()
	}

	const disableActions = matchTree === null || !matchTree.isComplete();

	return (
		<div className='wpbb-bracket-container'>
			{matchTree ? <PairedBracket matchTree={matchTree} setMatchTree={setMatchTree} canPick ref={bracketRef} /> : 'Loading...'}
			<div className={'wpbb-bracket-actions'}>
				<ApparelButton disabled={disableActions} onClick={handleApparelClick} />
			</div>
		</div>
	)
}


export default UserBracket;