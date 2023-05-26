import React, { useState, useEffect, useRef } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';

import { MatchTree, WildcardPlacement } from '../../bracket/models/MatchTree';
import { BracketRes, SubmissionReq } from '../../api/types/bracket';


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
	apparelUrl?: string;
}

const UserBracket = (props: UserBracketProps) => {
	const {
		bracketId,
		bracketRes,
		apparelUrl,
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

	const buildPrintHTML = (innerHTML: string, styleUrl: string, inchHeight: number, inchWidth: number,) => {
		const printArea = buildPrintArea(innerHTML, inchHeight, inchWidth)
		// const stylesheet = 'https://backmybracket.com/wp-content/plugins/wp-bracket-builder/includes/react-bracket-builder/build/index.css'
		const stylesheet = 'https://wpbb-stylesheets.s3.amazonaws.com/index.css'
		const styles = getPrintStyles();
		return `
			<html>
				<head>
					<link rel='stylesheet' href='${stylesheet}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
		`.replace(/[\n\t]/g, '').replace(/"/g, "'")
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
					}
				}
			</style>
			`
	}


	const buildPrintArea = (innerHTML: string, inchHeight: number, inchWidth: number) => {
		const width = inchWidth * 96;
		const height = inchHeight * 96;
		return `
			<div class='wpbb-bracket-print-area' style='height: ${height}px; width: ${width}px; background-color: transparent'>
				${innerHTML}
			</div>
		`
	}

	const getHTML = (): string => {
		const bracketEl = document.getElementsByClassName('wpbb-bracket')[0]
		const bracketHTML = bracketEl.outerHTML
		const printArea = buildPrintArea(bracketHTML, 16, 12)
		//@ts-ignore
		const bracketCss = wpbb_ajax_obj.css_file
		const html = buildPrintHTML(printArea, bracketCss, 16, 12)
		return html
	}

	const getStyles = () => {
		const sheets = document.styleSheets;
	}


	const handleApparelClick = () => {
		const id = bracketId || bracketRes?.id;
		if (!id || !matchTree) {
			console.error('no bracket id or match tree')
			return;
		}
		console.log('apparel click')
		const html = getHTML()
		// const roundReqs = matchTree.toSubmissionReq();
		// const submissionReq: SubmissionReq = {
		// 	name: 'test submission',
		// 	bracketId: id,
		// 	rounds: roundReqs,
		// 	html: html,
		// }
		console.log('apparel url')
		console.log(apparelUrl)

		// bracketApi.htmlToImage({ html: html, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1 }).then((res) => {
		// 	console.log('res')
		// 	console.log(res)
		// 	console.log('hi')
		// 	// navigate to the product page
		// 	console.log('apparel url')
		// 	console.log(apparelUrl)

		// 	//open new tab with imageURL
		// 	// const newWindow = window.open(res.imageUrl)
		// })

		// const newWindow = window.open();
		// newWindow?.document.write(html);
		// newWindow?.document.close();

	}

	// const disableActions = matchTree === null || !matchTree.isComplete();
	const disableActions = false

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