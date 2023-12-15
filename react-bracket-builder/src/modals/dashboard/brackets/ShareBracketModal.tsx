import { ModalHeader } from '../../ModalHeader'
import { CancelButton, ConfirmButton, Link } from '../../ModalButtons'
import React, { useState } from 'react'
import { Modal } from '../../Modal'
import addClickHandlers from '../../addClickHandlers'
import { ReactComponent as LinkIcon } from '../../../brackets/shared/assets/link.svg'
import { ReactComponent as XLogo } from '../../../brackets/shared/assets/x-logo.svg'
import { ReactComponent as FacebookLogo } from '../../../brackets/shared/assets/facebook-logo.svg'

export default function ShareBracketModal() {
  const [show, setShow] = useState(false)
  const [playBracketUrl, setPlayBracketUrl] = useState('')
  const [bracketTitle, setBracketTitle] = useState('')
  addClickHandlers({
    buttonClassName: 'wpbb-share-bracket-button',
    onButtonClick: (b) => {
      setPlayBracketUrl(b.dataset.playBracketUrl)
      setBracketTitle(b.dataset.bracketTitle)
      setShow(true)
    },
  })
  const shareText = `Play my bracket, ${bracketTitle.toUpperCase()}!`
  const twitterShareUrl = `https://twitter.com/intent/tweet?text=${shareText}&url=${playBracketUrl}&via=BackMyBracket`
  const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${playBracketUrl}`
  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={'Share Bracket'} />
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-gap-10 tw-items-center">
          <Link href={facebookShareUrl} openInNewTab={true}>
            <FacebookLogo />
          </Link>
          <Link href={twitterShareUrl} openInNewTab={true}>
            <XLogo />
          </Link>
        </div>
        <ConfirmButton
          disabled={false}
          onClick={() => {
            navigator.clipboard.writeText(playBracketUrl).catch((err) => {
              console.error(err)
              console.error(
                'If "navigator.clipboard is undefined", you may not be using a secure origin.'
              )
            })
            setShow(false)
          }}
        >
          <LinkIcon />
          <span>Copy link</span>
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
