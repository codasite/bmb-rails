import React, { useState, useEffect, useRef } from 'react';
import * as Sentry from '@sentry/react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
import Spinner from 'react-bootstrap/Spinner'
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';

import { MatchTree, WildcardPlacement } from '../../bracket/models/MatchTree';
import { BracketRes, SubmissionReq } from '../../api/types/bracket';


//@ts-ignore

interface BuyApparelBtnProps {
	onClick?: () => void;
	disabled?: boolean;
	loading?: boolean;
}

const ApparelButton = (props: BuyApparelBtnProps) => {
	const {
		disabled,
		onClick = () => { },
		loading,
	} = props;
	return (
		<button className={'wpbb-apparel-btn' + (disabled ? '-disabled' : '')} onClick={onClick} disabled={disabled}>
			{props.loading ?
				//@ts-ignore
				<Spinner variant='light' animation="border" role="status" style={{ borderWidth: '4px' }} />
				: 'ADD TO APPAREL'}
		</button>
	)
}

interface UserBracketProps {
	bracketId?: number;
	bracketRes?: BracketRes;
	apparelUrl: string;
}

const UserBracket = (props: UserBracketProps) => {
	const {
		bracketId,
		bracketRes,
		apparelUrl,
	} = props;

	const [matchTree, setMatchTree] = useState<Nullable<MatchTree>>(null);
	const [processingImage, setProcessingImage] = useState(false);

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
		const html = getHTML()

		setProcessingImage(true)
		bracketApi.htmlToImage({ html: html, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1 }).then((res) => {
			// redirect to apparel page
			console.log(apparelUrl)
			window.location.href = apparelUrl
		}).catch((err) => {
			setProcessingImage(false)
			console.error(err)
			Sentry.captureException(err)
		})
	}

	const disableActions = matchTree === null || !matchTree.isComplete() || processingImage
	// const disableActions = processingImage
	const numRounds = matchTree?.rounds.length;
	const pickedWinner = matchTree?.isComplete();

	return (
		<div className={`wpbb-bracket-container wpbb-${numRounds}-rounds`}>
			{matchTree ? [
				<div className={'wpbb-slogan-container' + (pickedWinner ? ' invisible' : ' visible')}>
					<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
				</div>,
				<PairedBracket matchTree={matchTree} setMatchTree={setMatchTree} canPick />,
				<div className={`wpbb-bracket-actions wpbb-${numRounds}-rounds`}>
					<ApparelButton disabled={disableActions} loading={processingImage} onClick={handleApparelClick} />
				</div>
			] : 'Loading...'}
		</div>
	)
}


export default UserBracket;