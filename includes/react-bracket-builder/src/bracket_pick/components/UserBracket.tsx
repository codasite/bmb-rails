import React, { useState, useEffect } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../api/bracketApi';
import { useWindowDimensions } from '../../utils/hooks';
import Spinner from 'react-bootstrap/Spinner'
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';
import { PaginatedBracket } from '../../bracket/components/PaginatedBracket';
import { useAppSelector, useAppDispatch } from '../../app/hooks';
import { setMatchTree, selectMatchTree } from '../../features/match_tree/matchTreeSlice';

import { MatchTree } from '../../bracket/models/MatchTree';
import { BracketRes } from '../../api/types/bracket';
import { bracketConstants } from '../../bracket/constants';

import { NavButton } from '../../bracket/components/PaginatedBracket';

const {
	paginatedBracketWidth,
} = bracketConstants


//@ts-ignore

interface ThemeSelectorProps {
	darkMode: boolean;
	setDarkMode: (darkMode: boolean) => void;
}

const ThemeSelector = (props: ThemeSelectorProps) => {
	const {
		darkMode,
		setDarkMode,
	} = props;
	return (
		<div className='wpbb-theme-selector'>
			<span className='wpbb-theme-selector-text'>Theme</span>
			<div className='wpbb-theme-selector-switch-outer'>
				<div className='wpbb-theme-selector-switch-inner' onClick={() => setDarkMode(!darkMode)}>
					<span className='wpbb-theme-selector-switch-text'>{darkMode ? 'dark' : 'light'}</span>
				</div>
			</div>
		</div>
	)
}


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
		<button className={'wpbb-apparel-btn' + (disabled ? ' disabled' : '')} onClick={onClick} disabled={disabled}>
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
	bracketStylesheetUrl: string;
}

const UserBracket = (props: UserBracketProps) => {
	const {
		bracketId,
		bracketRes,
		apparelUrl,
		bracketStylesheetUrl,
	} = props;

	// const [matchTree, setMatchTree] = useState<Nullable<MatchTree>>(null);
	const [processingImage, setProcessingImage] = useState(false);
	const [darkMode, setDarkMode] = useState(true);
	const { width: windowWidth, height: windowHeight } = useWindowDimensions(); // custom hook to get window dimensions
	// const rounds = useAppSelector((state) => state.matchTree.rounds);
	const matchTree = useAppSelector(selectMatchTree);
	console.log(matchTree)
	const dispatch = useAppDispatch();

	useEffect(() => {
		if (bracketId) {
			bracketApi.getBracket(bracketId).then((res) => {
				console.log(res.rounds)
				// setMatchTree(MatchTree.fromRounds(res.rounds));
				dispatch(setMatchTree(res.rounds))
			});
		} else if (bracketRes) {
			console.log(bracketRes.rounds)
			// setMatchTree(MatchTree.fromRounds(bracketRes.rounds));
			dispatch(setMatchTree(bracketRes.rounds))
		}
	}, [bracketId, bracketRes]);

	const buildPrintHTML = (innerHTML: string, styleUrl: string, inchHeight: number, inchWidth: number,) => {
		const printArea = buildPrintArea(innerHTML, inchHeight, inchWidth)
		// const stylesheet = 'https://backmybracket.com/wp-content/plugins/wp-bracket-builder/includes/react-bracket-builder/build/index.css'
		const stylesheet = 'https://wpbb-stylesheets.s3.amazonaws.com/index.css'
		return `
			<html>
				<head>
					<link rel='stylesheet' href='${stylesheet}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
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
		const bracketCss = bracketStylesheetUrl
		const html = buildPrintHTML(bracketHTML, bracketCss, 16, 12)
		return html
	}

	const minify = (html: string) => {
		return html.replace(/[\n\t]/g, '').replace(/"/g, "'")
	}

	const handleApparelClick = () => {
		const id = bracketId || bracketRes?.id;
		if (!id || !matchTree) {
			console.error('no bracket id or match tree')
			return;
		}
		const html = getHTML()
		console.log(minify(html))

		const key = Math.random().toString(36).substring(7);
		const req: SubmissionReq = {
			bracketId: id,
			html: minify(html),
			name: `bracket-pick-${key}`,
		}
		console.log(req)
		// setProcessingImage(true)

		// bracketApi.submitBracket(req).then((res) => {
		// 	console.log(res)
		// 	setProcessingImage(false)
		// }).catch((err) => {
		// 	console.error(err)
		// 	setProcessingImage(false)
		// })




		// const parser = new DOMParser();
		// const doc = parser.parseFromString(html, 'text/html');
		// const bracketEl = doc.getElementsByClassName('wpbb-bracket')[0]
		// const printArea = doc.getElementsByClassName('wpbb-bracket-print-area')[0]

		// // if we were in dark mode, remove it to get the light mode version
		// bracketEl.classList.remove('wpbb-dark-mode')
		// const lightModeTopHTML = minify(doc.documentElement.outerHTML)
		// printArea.classList.add('wpbb-print-center')
		// const lightModeCenterHTML = minify(doc.documentElement.outerHTML)
		// bracketEl.classList.add('wpbb-dark-mode')
		// const darkModeCenterHTML = minify(doc.documentElement.outerHTML)
		// printArea.classList.remove('wpbb-print-center')
		// const darkModeTopHTML = minify(doc.documentElement.outerHTML)
		// 

		// console.log('light mode top', lightModeTopHTML)
		// console.log('light mode center', lightModeCenterHTML)
		// console.log('dark mode top', darkModeTopHTML)
		// console.log('dark mode center', darkModeCenterHTML)

		// Random key to link the two images together
		// const key = Math.random().toString(36).substring(7);
		// const promises = [
		// 	bracketApi.htmlToImage({ html: darkModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'top', s3Key: `bracket-${key}-dark-top.png` }),
		// 	bracketApi.htmlToImage({ html: lightModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'top', s3Key: `bracket-${key}-light-top.png` }),
		// 	bracketApi.htmlToImage({ html: darkModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'center', s3Key: `bracket-${key}-dark-center.png` }),
		// 	bracketApi.htmlToImage({ html: lightModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'center', s3Key: `bracket-${key}-light-center.png` }),
		// ]
		// setProcessingImage(true)
		// Promise.all(promises).then((res) => {
		// 	// console.log('res')
		// 	// console.log(res)
		// 	// setProcessingImage(false)
		// 	window.location.href = apparelUrl
		// }).catch((err) => {
		// 	setProcessingImage(false)
		// 	console.error(err)
		// 	Sentry.captureException(err)
		// })
	}

	const renderPairedBracket = (bracketProps) => {
		const { matchTree } = bracketProps
		if (!matchTree) {
			return <></>
		}
		const disableActions = matchTree === null || !matchTree.isComplete() || processingImage
		// const disableActions = processingImage
		const numRounds = matchTree?.rounds.length;
		const pickedWinner = matchTree?.isComplete();
		return (
			<div className={`wpbb-bracket-container wpbb-${numRounds}-rounds${darkMode ? ' wpbb-dark-mode' : ''}`}>
				{matchTree ? [
					<ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />,
					<div className={'wpbb-slogan-container' + (pickedWinner ? ' invisible' : ' visible')}>
						<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
					</div>,
					// <PairedBracket matchTree={matchTree} setMatchTree={setMatchTree} canPick darkMode={darkMode} bracketName={bracketRes?.name} />,
					<PairedBracket {...bracketProps} />,
					<div className={`wpbb-bracket-actions wpbb-${numRounds}-rounds`}>
						<ApparelButton disabled={disableActions} loading={processingImage} onClick={handleApparelClick} />
					</div>
				] : 'Loading...'}
			</div>
		)
	}

	const renderPaginatedBracket = (bracketProps) => {
		const { matchTree } = bracketProps
		if (!matchTree) {
			return <></>
		}
		const disableActions = matchTree === null || !matchTree.isComplete() || processingImage
		// const disableActions = processingImage
		const numRounds = matchTree?.rounds.length;
		const pickedWinner = matchTree?.isComplete();

		return (
			// <div className={`wpbb-paginated-bracket-container wpbb-${numRounds}-rounds${darkMode ? ' wpbb-dark-mode' : ''}`}>
			<div className={`wpbb-img-background wpbb-paginated-bracket-container wpbb-dark-mode`}>
				{matchTree ? [
					// <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />,
					// <div className={'wpbb-slogan-container' + (pickedWinner ? ' invisible' : ' visible')}>
					// 	<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
					// </div>,
					<PaginatedBracket {...bracketProps} />,
					<NavButton />,
				] : 'Loading...'}
			</div>
		)
	}


	const bracketProps = {
		matchTree,
		setMatchTree: (matchTree: MatchTree) => dispatch(setMatchTree(matchTree.toSerializable())),
		canPick: true,
		darkMode,
		bracketName: bracketRes?.name,
	}

	if (windowWidth < paginatedBracketWidth) {
		return renderPaginatedBracket(bracketProps)
	}
	return renderPairedBracket(bracketProps)
}


export default UserBracket;