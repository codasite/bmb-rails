import { ReactComponent as LogoDark } from '../../../brackets/shared/assets/logo_dark.svg'
import { ReactComponent as CheckIcon } from '../../../brackets/shared/assets/check.svg'
import * as React from 'react'
import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { CancelButton } from '../../ModalButtons'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { getDashboardPath } from '../../../brackets/shared'

interface PaywallModalProps {
  upgradeAccountUrl: string
  setShow: (show: boolean) => void
}

const PaywallModalContent = (props: PaywallModalProps) => {
  const { upgradeAccountUrl, setShow } = props
  return (
    <div className="tw-text-center">
      <LogoDark />
      <div className="tw-flex tw-items-end tw-justify-center tw-mt-24">
        <span className="tw-text-24">$</span>
        <span className="tw-text-64 tw-font-700 tw-leading-none">49</span>
      </div>
      <p className="tw-text-12 tw-font-700 tw-m-0 tw-mb-12">Yearly</p>
      <ul className="tw-list-none tw-p-0 tw-m-0 tw-text-16 tw-font-700">
        <li className="tw-flex tw-gap-10 tw-items-center tw-justify-center tw-py-4">
          <CheckIcon className="tw-text-red" />
          <span>Host and score brackets</span>
        </li>
        <li className="tw-flex tw-gap-10 tw-items-center tw-justify-center tw-py-4">
          <CheckIcon className="tw-text-red" />
          <span>Invite and compete with friends</span>
        </li>
      </ul>
      <a
        href={upgradeAccountUrl}
        className="tw-border-solid tw-border tw-border-green tw-bg-green/15 tw-flex tw-items-center tw-justify-center tw-rounded-8 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-leading-none tw-font-700 tw-mb-15 tw-mt-24 tw-p-12 tw-cursor-pointer"
      >
        Upgrade account
      </a>
      <CancelButton onClick={() => setShow(false)} />
    </div>
  )
}

export const PublishBracketModal = (props: {
  upgradeAccountUrl: string
  canCreateBracket: boolean
}) => {
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-publish-bracket-button',
    onButtonClick: (b) => {
      if (props.canCreateBracket) {
        const bracketId = parseInt(b.dataset.bracketId)
        bracketApi
          .updateBracket(bracketId, { status: 'publish' })
          .then((res) => {
            window.location.href = getDashboardPath()
          })
          .catch((err) => {
            console.error(err)
          })
      } else {
        setShow(true)
      }
    },
  })
  return (
    <Modal show={show} setShow={setShow}>
      <PaywallModalContent
        upgradeAccountUrl={props.upgradeAccountUrl}
        setShow={setShow}
      />
    </Modal>
  )
}
