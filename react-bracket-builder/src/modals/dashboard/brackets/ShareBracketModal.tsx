import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton, Link } from '../../ModalButtons'
// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { Modal } from '../../Modal'
import { ReactComponent as LinkIcon } from '../../../brackets/shared/assets/link.svg'
import { ReactComponent as XLogo } from '../../../brackets/shared/assets/x-logo.svg'
import { ReactComponent as FacebookLogo } from '../../../brackets/shared/assets/facebook-logo.svg'
import { BracketData } from './BracketData'

export const FacebookShareLink = (props: { playBracketUrl: string }) => {
  const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${props.playBracketUrl}`
  return (
    <Link href={facebookShareUrl} openInNewTab={true}>
      <FacebookLogo />
    </Link>
  )
}

export const TwitterShareLink = (props: {
  playBracketUrl: string
  bracketTitle: string
}) => {
  const shareText = `Play my bracket, ${props.bracketTitle?.toUpperCase()}!`
  const twitterShareUrl = `https://twitter.com/intent/tweet?text=${shareText}&url=${props.playBracketUrl}&via=BackMyBracket`
  return (
    <Link href={twitterShareUrl} openInNewTab={true}>
      <XLogo />
    </Link>
  )
}

export const CopyLinkButton = (props: {
  playBracketUrl: string
  onClick: () => void
}) => {
  return (
    <ConfirmButton
      disabled={false}
      onClick={() => {
        navigator.clipboard.writeText(props.playBracketUrl).catch((err) => {
          console.error(err)
          console.error(
            'If "navigator.clipboard is undefined", you may not be using a secure origin.'
          )
        })
        props.onClick()
      }}
    >
      <LinkIcon />
      <span>Copy link</span>
    </ConfirmButton>
  )
}

export const ShareBracketModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
}) => {
  console.log('ShareBracketModal', props.bracketData)
  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={'Share Bracket'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-gap-10 tw-items-center">
          <FacebookShareLink
            playBracketUrl={props.bracketData.playBracketUrl}
          />
          <TwitterShareLink
            playBracketUrl={props.bracketData.playBracketUrl}
            bracketTitle={props.bracketData.title}
          />
        </div>
        <CopyLinkButton
          playBracketUrl={props.bracketData.playBracketUrl}
          onClick={() => props.setShow(false)}
        />
        <CancelButton onClick={() => props.setShow(false)} />
      </div>
    </Modal>
  )
}
