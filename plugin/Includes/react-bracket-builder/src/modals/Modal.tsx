import React from 'react'

export const Modal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  children: React.ReactNode
}) => {
  return (
    <>
      {props.show && (
        <div
          onClick={() => props.setShow(false)}
          tabIndex={-1}
          className="tw-fixed tw-bg-black/50 tw-top-0 tw-left-0 tw-right-0 tw-z-50 tw-w-full tw-p-4 tw-overflow-x-hidden tw-overflow-y-auto md:tw-inset-0 tw-h-[calc(100%-1rem)] tw-max-h-full tw-justify-center tw-items-center tw-flex"
        >
          <div
            onClick={(e) => e.stopPropagation()}
            className="tw-relative tw-max-w-[606px] tw-max-h-full tw-p-60 tw-rounded-16 tw-bg-dark-blue tw-grow tw-mx-20"
          >
            {props.children}
          </div>
        </div>
      )}
    </>
  )
}
