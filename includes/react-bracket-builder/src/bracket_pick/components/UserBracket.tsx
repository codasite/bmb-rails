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


//@ts-ignore

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

	useEffect(() => {
		if (bracketId) {
			bracketApi.getBracket(bracketId).then((res) => {
				setMatchTree(MatchTree.fromRounds(res.rounds));
			});
		} else if (bracketRes) {
			setMatchTree(MatchTree.fromRounds(bracketRes.rounds));
		}
	}, [bracketId, bracketRes]);

	const buildPrintHTML = (innerHTML: string, styleUrl: string, inchWidth: number, inchHeight: number) => {
		const printArea = buildPrintArea(inchWidth, inchHeight, innerHTML)
		return `
			<html>
				<head>
					<link rel='stylesheet' href='${styleUrl}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
		`
	}

	const getPrintStyles = () => {
		return `
			<style>
				@page {
					size: 12in 16in;
					margin: 0;
				}
				@media print {
					.wpbb-bracket-print-area {
						page-break-after: always;
					}
				}
			</style>
			`
	}


	const buildPrintArea = (inchWidth: number, inchHeight: number, innerHTML: string) => {
		const width = inchWidth * 96;
		const height = inchHeight * 96;
		return `
			<div class='wpbb-bracket-print-area' style='height: ${height}px; width: ${width}px'>
				${innerHTML}
			</div>
		`
	}

	const getImage = () => {
		// const bracketEl: HTMLDivElement | null = bracketRef.current
		// if (!bracketEl) {
		// 	return
		// }
		console.log('getting with class')
		const bracketEl = document.getElementsByClassName('wpbb-bracket')[0]
		const bracketHTML = bracketEl.outerHTML
		const printArea = buildPrintArea(12, 16, bracketHTML)
		//@ts-ignore
		const bracketCss = wpbb_ajax_obj.css_file
		const printBody = buildPrintHTML(printArea, bracketCss)
		// console.log(bracketHTML)
		// console.log(printArea)
		console.log(printBody)
		// const userBracket = matchTree.toUserRequest('barry bracket', 999);
		// const json = JSON.stringify(userBracket);
		// console.log(json)
	}

	const getStyles = () => {
		const sheets = document.styleSheets;
		console.log(sheets)
	}


	const handleApparelClick = () => {
		console.log('apparel click')
		// getImage()
		// getStyles()
		console.log('bracket css', bracketCss)
	}

	const disableActions = matchTree === null || !matchTree.isComplete();

	return (
		<div className='wpbb-bracket-container'>
			{matchTree ? <PairedBracket matchTree={matchTree} setMatchTree={setMatchTree} canPick /> : 'Loading...'}
			<div className={'wpbb-bracket-actions'}>
				<ApparelButton disabled={disableActions} onClick={handleApparelClick} />
			</div>
		</div>
	)
}


export default UserBracket;